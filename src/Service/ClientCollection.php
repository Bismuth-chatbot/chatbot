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
        // TODO: Implement sendMessage() method.
    }
    
    public function emit(string $messageType, array $content): void
    {
        // TODO: Implement emit() method.
    }
    
    public function get(string $service): IClient
    {
        /** @var IClient $client */
        foreach ($this->clients as $client) {
            if ($client->getType() === $service) {
                return $client;
            }
        }
        throw new ClientServiceNotFoundException();
    }
    
    public function getType(): string
    {
        return ClientCollection::class;
    }
}
