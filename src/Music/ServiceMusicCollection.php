<?php
declare(strict_types=1);

namespace App\Music;

use App\Music\Exception\NoMusicPlayingException;

class ServiceMusicCollection implements IClient
{
    private iterable $clients;
    
    public function __construct(iterable $clients)
    {
        
        $this->clients = $clients;
    }
    
    public function getCurrentTrack()
    {
        /** @var IClient $client */
        foreach ($this->clients as $client) {
            try {
                return $client->getCurrentTrack();
            } catch (NoMusicPlayingException $e) {
                throw new NoMusicPlayingException();
            }
            
        }
    }
    
    public function get(string $service): IClient
    {
        foreach ($this->clients as $client) {
            if (get_class($client) === $service) {
                return $client;
            }
        }
    }
}
