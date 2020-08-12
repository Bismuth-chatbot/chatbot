<?php

namespace App\Command;

use App\Mercure\Consumer as MercureConsumer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Logger extends Command
{
    private $mercureConsumer;
    private $logger;

    public function __construct(MercureConsumer $mercureConsumer, LoggerInterface $logger)
    {
        $this->mercureConsumer = $mercureConsumer;
        $this->logger = $logger;
        parent::__construct();
    }

    protected static $defaultName = 'app:logger';

    protected function configure()
    {
        $this
            ->setDescription('Logs every command published on the mercure hub')
            ->addArgument('channel', InputArgument::REQUIRED, 'The twitch channel to subscribe to.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $topics = ['https://twitch.tv/'.$input->getArgument('channel')];

        foreach ($this->mercureConsumer->__invoke($topics) as $data) {
            if ($data->isCommand()) {
                $this->logger->info(sprintf('Got a "%s" command from "%s" on the channel "%s"', $data->getCommand(), $data->getNickname(), $data->getChannel()));
            }
        }

        return Command::SUCCESS;
    }
}
