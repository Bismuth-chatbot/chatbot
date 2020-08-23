<?php
declare(strict_types=1);

namespace App\Service;

use App\Service\Exception\ClientServiceNotFoundException;

class ClientCollection implements IClient
{
    private iterable $clients;
    
    public function __construct(iterable $clients)
    {
        $this->clients = $clients;
    }
    
    public function sendMessage(string $message): void
    {
        /** @var IClient $client */
        foreach ($this->clients as $client){
            $client->sendMessage($message);
        }
    }
    
    public function emit(string $messageType, array $content): void
    {
        /** @var IClient $client */
        foreach ($this->clients as $client){
            $client->emit($messageType, $content);
        }
    }
    
    public function get(string $service): IClient
    {
        /** @var IClient $client */
        foreach ($this->clients as $client) {
            if (get_class($client) === $service) {
                return $client;
            }
        }
        throw new ClientServiceNotFoundException();
    }
    
    
}
