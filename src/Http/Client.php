<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Client
{
    private HttpClientInterface $client;
    private string $httpHost;

    public function __construct(HttpClientInterface $client, string $httpHost)
    {
        $this->client = $client;
        $this->httpHost = $httpHost;
    }

    public function postMessage(string $service, string $message)
    {
        $response = $this->client->request('POST', 'http://'.$this->httpHost.'/'.$service, [
            'json' => ['message' => $message],
        ]);

        return $response;
    }
}
