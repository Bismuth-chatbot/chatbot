<?php

namespace App\Command;

use App\Mercure\Consumer as MercureConsumer;
use App\Twitch\Client as TwitchClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Dice extends Command
{
    private $mercureConsumer;
    private $twitchClient;

    public function __construct(TwitchClient $twitchClient, MercureConsumer $mercureConsumer)
    {
        $this->mercureConsumer = $mercureConsumer;
        $this->twitchClient = $twitchClient;
        parent::__construct();
    }

    protected static $defaultName = 'app:dice';

    protected function configure()
    {
        $this
            ->setDescription('Rolls a dice and answer to twitch when requested.')
            ->addArgument('channel', InputArgument::REQUIRED, 'The twitch channel to write to.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $topics = [sprintf('https://twitch.tv/%s/command/dice', $input->getArgument('channel'))];
        $this->twitchClient->connect();

        foreach ($this->mercureConsumer->__invoke($topics) as $message) {
            $dice = $message->getCommandArguments()[0] ?? 6;
            $rand = random_int(1, $dice);
            $this->twitchClient->sendMessage(sprintf('%s sent a %d dice resulting in a %d', $message->getNickname(), $dice, $rand));
        }

        return Command::SUCCESS;
    }
}
