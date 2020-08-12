<?php

namespace App\Mercure;

use App\Message\Message as Message;
use App\Twitch\Message as TwitchMessage;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\Serializer\SerializerInterface;

final class Consumer
{
    private $client;

    public function __construct(EventSourceHttpClient $client, SerializerInterface $serializer, $mercureHubUrl)
    {
        $this->mercureHubUrl = $mercureHubUrl;
        $this->client = $client;
        $this->serializer = $serializer;
    }

    /**
     * @return ArrayIterator|App\Message\MessageInterface[]
     */
    public function __invoke(array $topics): \Iterator
    {
        $url = $this->mercureHubUrl.'?topic='.implode('&topic=', $topics);

        $source = $this->client->connect($url);
        while ($source) {
            foreach ($this->client->stream($source, 2) as $r => $chunk) {
                if ($chunk->isTimeout()) {
                    continue;
                }

                if ($chunk->isLast()) {
                    $source = null;

                    return;
                }

                if ($chunk instanceof ServerSentEvent) {
                    yield $this->serializer->deserialize($chunk->getData(), $this->getMessageClass($topics), 'json');
                }
            }
        }

        return;
    }

    private function getMessageClass(array $topics): string
    {
        if (strpos($topics[0], 'twitch.tv')) {
            return TwitchMessage::class;
        }

        return Message::class;
    }
}
