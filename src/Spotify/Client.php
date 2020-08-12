<?php

namespace App\Spotify;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Client
{
    private $spotifyToken;
    private $client;
    private $logger;
    private $token;

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

        $track = json_decode($response->getContent(false));

        $str = $track->item->name.' ('.$track->item->album->name.') by ';

        foreach ($track->item->artists as $i => $artist) {
            $str .= 0 === $i ? $artist->name : ', '.$artist->name;
        }

        return $str;
    }
}
