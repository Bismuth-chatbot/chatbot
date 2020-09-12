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

require './vendor/autoload.php';
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;

$url = 'http://localhost:8081';
$client = new EventSourceHttpClient();
$source = $client->connect($url);
while ($source) {
    foreach ($client->stream($source, 2) as $r => $chunk) {
        if ($chunk->isTimeout() || $chunk->isFirst()) {
            continue;
        }
        if ($chunk->isLast()) {
            $source = null;

            return;
        }
        if ($chunk instanceof ServerSentEvent) {
            var_dump($chunk->getData());
        }
    }
}
