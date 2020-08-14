<?php

declare(strict_types=1);

namespace App\Command;

use App\Http\Server as HttpServer;
use App\Twitch\Client as TwitchClient;
use Psr\Http\Message\ServerRequestInterface;
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

    public function __construct(
        TwitchClient $twitchClient,
        Publisher $publisher,
        SerializerInterface $serializer,
        HttpServer $httpServer
    ) {
        $this->twitchClient = $twitchClient;
        $this->publisher = $publisher;
        $this->serializer = $serializer;
        $this->httpServer = $httpServer;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $loop = EventLoopFactory::create();

        $this->httpServer->run($loop, function (ServerRequestInterface $request) use ($io) {
            if (false === strpos($request->getHeader('content-type')[0], 'application/json')) {
                return new Response(400);
            }

            switch ($request->getUri()->getPath()) {
                case '/twitch':
                    try {
                        $body = json_decode($request->getBody()->getContents(), true);
                        $this->twitchClient->sendMessage($body['message']);

                        return new Response(202);
                    } catch (\Exception $e) {
                        $io->error($e->getMessage());

                        return new Response(500);
                    }
            }
        });

        $clientSocket = $this->twitchClient->connect($loop);
        $clientSocket->on('data', function ($data) {
            foreach ($this->twitchClient->parse($data) as $message) {
                $channel = substr($message->getChannel(), 1); // remove #
                $topics = [sprintf('https://twitch.tv/%s', $channel)];
                if ($message->isCommand()) {
                    $topics[] = sprintf('https://twitch.tv/%s/command/%s', $channel, $message->getCommand());
                }

                $this->publisher->__invoke(new Update($topics, $this->serializer->serialize($message, 'json')));
            }
        });

        $io->success('Server is up on '.$this->httpServer->getHttpHost());
        $loop->run();
    }
}
