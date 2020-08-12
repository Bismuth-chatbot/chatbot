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

final class Client
{
    private const TWITCH_IRC_URI = 'irc.chat.twitch.tv';
    private const TWITCH_IRC_PORT = 6667;
    /** @see https://discuss.dev.twitch.tv/t/missing-client-side-message-length-check/21316 */
    private const MAX_LINE = 512;
    private $socket;
    private LoggerInterface $logger;
    private string $channel;
    private string $oauthToken;
    private string $botUsername;
    private ?string $message;

    public function __construct(string $oauthToken, string $botUsername, string $channel)
    {
        $this->logger = new NullLogger();
        $this->channel = $channel;
        $this->oauthToken = $oauthToken;
        $this->botUsername = $botUsername;
        $this->channel = $channel;
    }

    public function connect()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        $this->logger->info(sprintf('Connecting onto %s:%s on channel %s as %s', self::TWITCH_IRC_URI, self::TWITCH_IRC_PORT, $this->channel, $this->botUsername));
        if (false === socket_connect($this->socket, self::TWITCH_IRC_URI, self::TWITCH_IRC_PORT)) {
            throw new TwitchConnectionFailedException($this->getError());
        }

        $this->send(sprintf('PASS %s', $this->oauthToken));
        $this->send(sprintf('NICK %s', $this->botUsername));
        $this->send(sprintf('JOIN #%s', $this->channel));
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
    }

    public function send(string $message): void
    {
        if (!$this->isConnected()) {
            throw new TwitchConnectionFailedException($this->getError());
        }

        $this->logger->info('send octets '.socket_write($this->socket, $message." \r\n"));
    }

    public function read(): \Iterator
    {
        if (!$this->isConnected()) {
            throw new TwitchConnectionFailedException($this->getError());
        }

        $data = socket_read($this->socket, self::MAX_LINE);
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
        socket_close($this->socket);
        $this->socket = null;
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
        return null !== $this->socket;
    }
}
