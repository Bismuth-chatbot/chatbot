<?php
declare(strict_types=1);

namespace App\Music\SoundCloud;

use App\Music\IClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client implements IClient
{
    /**
     * @var HttpClientInterface
     */
    private HttpClientInterface $client;
    
    public function __construct(HttpClientInterface $client)
    {
        
        $this->client = $client;
    }
    
    public function getCurrentTrack()
    {
        
        $this->client->request('GET', 'https://api.soundcloud.com/me/activities?limit=1');
        
    }
    
    public function get(string $service): IClient
    {
        return $this;
    }
}
