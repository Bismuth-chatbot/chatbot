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

use App\Spotify\Client as SpotifyClient;
use App\Transport\TransportInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CurrentSpotifyTrack extends Command
{
    private SpotifyClient $spotifyClient;
    private TransportInterface $transport;

    public function __construct(TransportInterface $transport, SpotifyClient $spotifyClient)
    {
        $this->transport = $transport;
        $this->spotifyClient = $spotifyClient;
        parent::__construct();
    }

    protected static $defaultName = 'app:current-spotify-track';

    protected function configure()
    {
        $this
            ->setDescription('User\'s Currently Playing Track.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->transport->commands('music') as $message) {
            $this->transport->send('Current track: '.$this->spotifyClient->getCurrentTrack());
        }

        return Command::SUCCESS;
    }
}
