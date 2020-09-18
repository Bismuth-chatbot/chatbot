<?php
declare(strict_types=1);

namespace App\Command;

use App\Http\Client as HttpClient;
use App\Mercure\Consumer as MercureConsumer;
use App\Twitch\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Emote extends Command
{
    protected static $defaultName = 'app:emote';
    private string $twitchChannel;
    /**
     * @var MercureConsumer
     */
    private MercureConsumer $consumer;
    /**
     * @var HttpClient
     */
    private HttpClient $client;
    
    public function __construct(string $twitchChannel, MercureConsumer $consumer, HttpClient $client)
    {
        parent::__construct(null);
        $this->twitchChannel = $twitchChannel;
        $this->consumer = $consumer;
        $this->client = $client;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $emotes = ['LUL'];
        $topics = [sprintf('https://twitch.tv/%s/command/emote', $this->twitchChannel)];
        $nbEmotes = 0;
        /** @var Message $message */
        foreach ($this->consumer->__invoke($topics) as $message) {
            
            foreach (explode(' ', $message->getMessage()) as $word) {
                if (in_array($word, $emotes)) {
                    if ($nbEmotes === 0) {
                        $this->client->postMessage('twitch', $word);
                    }
                    $nbEmotes++;
                    if ($nbEmotes === 5) {
                        $this->client->postMessage('twitch', $word);
                        $nbEmotes = 0;
                    }
                }
            }
        }
    }
}
