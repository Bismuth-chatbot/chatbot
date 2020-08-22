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
        return $this->client->request('POST', 'http://'.$this->httpHost.'/'.$service, [
            'json' => ['message' => $message],
        ]);
    }
}
