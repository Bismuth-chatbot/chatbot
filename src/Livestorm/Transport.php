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

namespace App\Livestorm;

use App\Mercure\Consumer as MercureConsumer;
use App\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Transport implements TransportInterface
{
    private MercureConsumer $mercureConsumer;
    private HttpClientInterface $httpClient;
    private string $httpHost;

    public function __construct(HttpClientInterface $httpClient, MercureConsumer $mercureConsumer)
    {
        $this->mercureConsumer = $mercureConsumer;
        $this->httpClient = $httpClient;
    }

    public function commands(string $command): \Iterator
    {
        $topics = [sprintf('https://app.livestorm.co/command/%s', $command)];
        foreach ($this->mercureConsumer->__invoke($topics) as $message) {
            yield $message;
        }
    }

    public function send(string $message): void
    {
        // not implemented
    }
}
