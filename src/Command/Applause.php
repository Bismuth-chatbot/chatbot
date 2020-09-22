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

namespace App\Command;

use App\Transport\TransportInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Applause extends Command
{
    private TransportInterface $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
        parent::__construct();
    }

    protected static $defaultName = 'app:applause';

    protected function configure()
    {
        $this
            ->setDescription('Run an applause sound when launched');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->transport->commands('applause') as $message) {
            if ($message->isCommand() && in_array($message->getCommand(), ['applause', 'bravo', 'gz', 'congrats', 'gj', 'merci'], true)) {
                exec(sprintf('ffplay -nodisp -autoexit %s >/dev/null 2>&1 & ', __DIR__.'/../../benchmarks/livestorm/applause.mp3'));
            }
        }

        return Command::SUCCESS;
    }
}
