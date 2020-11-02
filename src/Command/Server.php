<?php

/*
 * This file is part of the Bizmuth Bot project
 *
 * (c) Antoine Bluchet <antoine@bluchet.fr>
 * (c) Lemay Marc <flugv1@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Command;

use App\Http\Router\Exception\RouteNotFoundException;
use App\Http\Router\RoutesCollection;
use App\Http\Server as HttpServer;
use App\Mercure\Topic;
use App\Twitch\Client as TwitchClient;
use Psr\Http\Message\ServerRequestInterface;
use React\ChildProcess\Process;
use React\EventLoop\Factory as EventLoopFactory;
use React\Http\Message\Response;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mercure\Publisher;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

class Server extends Command
{
    protected static $defaultName = 'app:server:run';
    private TwitchClient $twitchClient;
    private Publisher $publisher;
    private SerializerInterface $serializer;
    private HttpServer $httpServer;
    private RoutesCollection $routes;
    private array $commands;

    public function __construct(
        TwitchClient $twitchClient,
        Publisher $publisher,
        SerializerInterface $serializer,
        HttpServer $httpServer,
        RoutesCollection $routes,
        array $commands
    ) {
        $this->twitchClient = $twitchClient;
        $this->publisher = $publisher;
        $this->serializer = $serializer;
        $this->httpServer = $httpServer;
        parent::__construct();
        $this->routes = $routes;
        $this->commands = $commands;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $loop = EventLoopFactory::create();
        $consoleBin = getcwd().'/bin/console';
        foreach ($this->commands as $command) {
            $process = new Process($consoleBin.' '.$command.' -vvv');
            $process->start($loop);
        }
        $clientSocket = $this->twitchClient->connect($loop);

        $loop->addPeriodicTimer(300, function () use ($io) {
            $this->twitchClient->pong();
            $io->success('send pong');
        });

        $this->httpServer->run($loop, function (ServerRequestInterface $request) use ($io) {
            if (false === strpos($request->getHeader('content-type')[0], 'application/json')) {
                return new Response(400);
            }
            try {
                return $this->routes->get($request->getMethod(), $request->getUri()->getPath())($this->twitchClient,
                    $request);
            } catch (RouteNotFoundException $e) {
                return new Response(404);
            } catch (\Exception $e) {
                $io->error([$e->getMessage(), $e->getLine(), $e->getFile()]);

                return new Response(500);
            }
        });
        $clientSocket->on('data', [$this->twitchClient, 'parse']);
        $clientSocket->on('message', function ($data) use ($io) {
            try {
                $message = json_decode((string) $data, true);
                $channel = '#' === $message['channel'][0] ? substr($message['channel'],
                    1) : $message['channel'];
                $topics = [
                    Topic::create(['<channel>' => $channel]),
                    Topic::create(['<channel>' => $channel, '<command>' => 'mediator']),
                    Topic::create(['<channel>' => $channel, '<command>' => 'emote']),
                    Topic::create(['<channel>' => $channel, '<command>' => 'findword']),
                ];
                if ((bool) $message['isCommand']) {
                    $topics[] = Topic::create(['<channel>' => $channel, '<command>' => $message['command']]);
                }
                $io->success('send message '.json_encode($message));
                $this->publisher->__invoke(new Update($topics, $data));
            } catch (\Exception $e) {
                $io->error($e->getMessage());
            }
        });
        $io->success('Server is up on '.$this->httpServer->getHttpHost());
        $loop->run();

        return Command::SUCCESS;
    }
}
