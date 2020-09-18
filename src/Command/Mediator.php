<?php
declare(strict_types=1);

namespace App\Command;

use App\Analisys\Comportment;
use App\Http\Client as HttpClient;
use App\Mercure\Consumer as MercureConsumer;
use App\Twitch\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Mediator extends Command
{
    protected static $defaultName = 'app:mediator';
    private string $twitchChannel;
    /**
     * @var MercureConsumer
     */
    private MercureConsumer $consumer;
    /**
     * @var Comportment
     */
    private Comportment $comportment;
    /**
     * @var HttpClient
     */
    private HttpClient $client;
    
    public function __construct(
        string $twitchChannel,
        MercureConsumer $consumer,
        Comportment $comportment,
        HttpClient $client
    ) {
        parent::__construct();
        $this->twitchChannel = $twitchChannel;
        $this->consumer = $consumer;
        $this->comportment = $comportment;
        $this->client = $client;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $topics = [sprintf('https://twitch.tv/%s/command/mediator', $this->twitchChannel)];
        $users = [];
        /** @var Message $message */
        foreach ($this->consumer->__invoke($topics) as $message) {
            
            $analyse = $this->comportment->analysis($message->getMessage());
            $io->success((string)$analyse);
            $io->success('Positivity score : ' . $analyse->getScorePositivity());
            if ($analyse->is(Comportment::NEGATIVE)) {
                !isset($users[$message->getNickname()]) ? $users[$message->getNickname()] = 1 : $users[$message->getNickname()]++;
                $responseMsg = sprintf('Attention @%s ça fait %s fois que tu mets un truc négatif sur le chat !',
                    $message->getNickname(),
                    $users[$message->getNickname()]
                );
                $this->client->postMessage('twitch', $responseMsg);
                if ($users[$message->getNickname()] >= 5) {
                    $this->client->postMessage('twitch', 'Si tu continues tu vas prendre tarif par les modos !');
                    $users[$message->getNickname()] = 0;
                }
                
            }
            
        }
        
        return Command::SUCCESS;
    }
}
