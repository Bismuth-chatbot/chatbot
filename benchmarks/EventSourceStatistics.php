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

echo getmypid();

$f = fopen('./number', 'w+');
$f2 = fopen('./persec', 'w+');

$i = 0;
$timestart = microtime(true);
$persec = 0;

fwrite($f2, "received;duration;tstart;tend\n");

$url = 'http://localhost:8081';
$client = new EventSourceHttpClient();
$source = $client->connect($url);
while ($source) {
    // try {
    foreach ($client->stream($source, 2) as $r => $chunk) {
        if ($chunk->isTimeout() || $chunk->isFirst()) {
            continue;
        }
        if ($chunk->isLast()) {
            $source = null;

            return;
        }
        if ($chunk instanceof ServerSentEvent) {
            $timeend = microtime(true);
            $duration = ($timeend - $timestart);
            ++$persec;

            fwrite($f, (string) ++$i);
            fseek($f, 0);

            if ($duration >= 1.0) {
                fwrite($f2, sprintf("%d;%d;%d;%d\n", $persec, $duration, $timestart, $timeend));
                $timestart = $timeend;
                $persec = 0;
            }
        }
    }
    // } catch (\LogicException $e) {
    //     $source->cancel();
    //     $source = $this->client->connect($url);
    // }
}
