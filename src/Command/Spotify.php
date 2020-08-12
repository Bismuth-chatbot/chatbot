<?php

namespace App\Command;

use App\Mercure\Consumer as MercureConsumer;
use App\Spotify\Client as SpotifyClient;
use App\Twitch\Client as TwitchClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Spotify extends Command
{
    private $mercureConsumer;
    private $twitchClient;
    private $spotifyClient;

    public function __construct(TwitchClient $twitchClient, MercureConsumer $mercureConsumer, SpotifyClient $spotifyClient)
    {
        $this->mercureConsumer = $mercureConsumer;
        $this->twitchClient = $twitchClient;
        $this->spotifyClient = $spotifyClient;
        parent::__construct();
    }

    protected static $defaultName = 'app:spotify';

    protected function configure()
    {
        $this
            ->setDescription('User\'s Currently Playing Track.')
            ->addArgument('channel', InputArgument::REQUIRED, 'The twitch channel to write to.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $topics = [sprintf('https://twitch.tv/%s/command/music', $input->getArgument('channel'))];
        $this->twitchClient->connect();

        foreach ($this->mercureConsumer->__invoke($topics) as $message) {
            $this->twitchClient->sendMessage('Current track: '.$this->spotifyClient->getCurrentTrack());
        }

        return Command::SUCCESS;
    }
}
