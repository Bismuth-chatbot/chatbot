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

namespace App\Music\Spotify;

use App\Music\Exception\NoMusicPlayingException;
use App\Music\IClient;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Client implements IClient
{
    private $spotifyToken;
    private $client;
    private $logger;

    public function __construct($spotifyToken, HttpClientInterface $client, LoggerInterface $logger)
    {
        $this->spotifyToken = $spotifyToken;
        $this->client = $client;
        $this->logger = $logger;
    }

    public function getCurrentTrack()
    {
        $response = $this->client->request('GET', 'https://api.spotify.com/v1/me/player/currently-playing', [
            'headers' => ['Authorization' => 'Bearer '.$this->spotifyToken],
        ]);
        $track = json_decode($response->getContent(false), true);

        if (!isset($track['item'])) {
            throw new NoMusicPlayingException();
        }
        $str = $track['item']['name'].' ('.$track['item']['album']['name'].') by ';
        foreach ($track['item']['artists'] as $i => $artist) {
            $str .= 0 === $i ? $artist['name'] : ', '.$artist['name'];
        }

        return $str;
    }

    public function get(string $service): IClient
    {
        return $this;
    }
}
