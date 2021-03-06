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

final class Dice extends Command
{
    private TransportInterface $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
        parent::__construct();
    }

    protected static $defaultName = 'app:dice';

    protected function configure()
    {
        $this
            ->setDescription('Rolls a dice and answer to twitch when requested.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->transport->commands('dice') as $message) {
            $dice = $message->getCommandArguments()[0] ?? 6;
            $rand = random_int(1, $dice);
            $this->transport->send(sprintf('%s sent a %d dice resulting in a %d',
                '@'.$message->getNickname(),
                $dice,
                $rand
            ));
        }

        return Command::SUCCESS;
    }
}
