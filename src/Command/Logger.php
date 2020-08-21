<?php

namespace App\Command;

use App\Mercure\Consumer as MercureConsumer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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
        $this->logger = new NullLogger();
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
        $output->writeln('PID: ' . getmypid());

        $f = fopen('./number', 'w+');
        $f2 = fopen('./persec', 'w+');
        $topics = ['https://twitch.tv/'.$input->getArgument('channel')];

        $i = 0;
        $timestart = microtime(true);
        $persec = 0;

        fwrite($f2, "received;duration;tstart;tend\n");

        foreach ($this->mercureConsumer->__invoke($topics) as $data) {
            if ($data->isCommand()) {
                $this->logger->info(sprintf('Got a "%s" command from "%s" on the channel "%s"', $data->getCommand(), $data->getNickname(), $data->getChannel()));
            }

            $timeend = microtime(true);
            $duration = ($timeend - $timestart); 
            $persec++;

            fwrite($f, ++$i);
            fseek($f, 0);
            
            if ($duration >= 1.0) {
                fwrite($f2, sprintf("%d;%d;%d;%d\n", $persec, $duration, $timestart, $timeend));
                $timestart = $timeend;
                $persec = 0;
            }
        }

        return Command::SUCCESS;
    }
}
