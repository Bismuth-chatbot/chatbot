<?php
/*
 * This file is part of the Chatbot project.
 *
 * (c) Lemay Marc <flugv1@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace App\Twitch;

use App\Exception\TwitchConnectionFailedException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Stream\DuplexResourceStream;
use React\Stream\DuplexStreamInterface;

final class Client
{
    private const TWITCH_IRC_URI = 'irc.chat.twitch.tv';
    private const TWITCH_IRC_PORT = 6667;
    /** @see https://discuss.dev.twitch.tv/t/missing-client-side-message-length-check/21316 */
    private const MAX_LINE = 512;
    private DuplexStreamInterface $socket;
    private LoggerInterface $logger;
    private string $channel;
    private string $oauthToken;
    private string $botUsername;
    private $loop;
    private bool $isRun = false;

    public function __construct(string $oauthToken, string $botUsername, string $channel)
    {
        $this->logger = new NullLogger();
        $this->channel = $channel;
        $this->oauthToken = $oauthToken;
        $this->botUsername = $botUsername;
        $this->channel = $channel;
        $this->loop = Factory::create();
    }

    public function connect(?LoopInterface $loop = null): DuplexStreamInterface
    {
        $stream = stream_socket_client(self::TWITCH_IRC_URI.':'.self::TWITCH_IRC_PORT);
        $this->socket = new DuplexResourceStream($stream, $loop ?? $this->loop, self::MAX_LINE);
        $this->logger->info(sprintf('Connecting onto %s:%s on channel %s as %s', self::TWITCH_IRC_URI,
            self::TWITCH_IRC_PORT, $this->channel, $this->botUsername));
        $this->send(sprintf('PASS %s', $this->oauthToken));
        $this->send(sprintf('NICK %s', $this->botUsername));
        $this->send(sprintf('JOIN #%s', $this->channel));

        return $this->socket;
    }

    public function isRun(): bool
    {
        return $this->isRun;
    }

    public function run(?float $withTimer = null)
    {
        if ($withTimer == !null) {
            $this->loop->addTimer($withTimer, function () {
                $this->loop->stop();
            });
        }
        $this->loop->run();
    }

    public function end($data = null)
    {
        $this->socket->end($data);
    }

    public function stop()
    {
        $this->loop->stop();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function ping(): void
    {
        $this->send(sprintf('PING :tmi.twitch.tv'));
    }

    public function pong(): void
    {
        $this->send(sprintf('PONG :tmi.twitch.tv'));
    }

    public function sendMessage(string $message): void
    {
        $this->send(sprintf('PRIVMSG #%s :%s', $this->channel, $message));
        $this->logger->info('send message '.$message." \r\n");
    }

    public function emit(string $messageType, array $content): void
    {
        $this->socket->emit($messageType, $content);
    }

    public function send(string $message): void
    {
        if (!$this->isConnected()) {
            throw new TwitchConnectionFailedException($this->getError());
        }
        $this->socket->write($message." \n");
    }

    public function parse(string $data): \Iterator
    {
        $messages = array_filter(preg_split('/[\r\n]/', $data), 'strlen');
        foreach ($messages as $message) {
            if (preg_match_all('/^:(.+?(?=!)).+ PRIVMSG (.+?(?=:)):(.+)$/', $message, $matches)) {
                $message = new Message($matches[3][0], $matches[1][0], trim($matches[2][0]));
                $this->logger->info(sprintf('Message: "%s"', $message));
                yield $message;
            }
        }
    }

    public function close()
    {
        $this->socket->close();
    }

    public function getError(): string
    {
        if ($this->socket) {
            return socket_strerror(socket_last_error($this->socket));
        }

        return 'No socket';
    }

    public function isConnected(): bool
    {
        return $this->socket->isReadable() && $this->socket->isWritable();
    }
}
