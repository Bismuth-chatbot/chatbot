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

use App\Http\Client as HttpClient;
use App\Mercure\Consumer as MercureConsumer;
use App\Spotify\Client as SpotifyClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Spotify extends Command
{
    private $mercureConsumer;
    private $spotifyClient;
    private string $twitchChannel;
    private HttpClient $httpClient;

    public function __construct(
        HttpClient $httpClient,
        MercureConsumer $mercureConsumer,
        SpotifyClient $spotifyClient,
        string $twitchChannel
    ) {
        $this->mercureConsumer = $mercureConsumer;
        $this->spotifyClient = $spotifyClient;
        $this->twitchChannel = $twitchChannel;
        $this->httpClient = $httpClient;
        parent::__construct();
    }

    protected static $defaultName = 'app:spotify';

    protected function configure()
    {
        $this
            ->setDescription('User\'s Currently Playing Track.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $topics = [sprintf('https://twitch.tv/%s/command/music', $this->twitchChannel)];
        foreach ($this->mercureConsumer->__invoke($topics) as $message) {
            $this->httpClient->postMessage('twitch', 'Current track: '.$this->spotifyClient->getCurrentTrack());
        }

        return Command::SUCCESS;
    }
}
