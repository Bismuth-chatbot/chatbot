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

namespace App\Twitch;

use App\Exception\TwitchConnectionFailedException;
use App\Service\IClient;
use App\Service\ISpecialMessage;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;
use React\Stream\DuplexResourceStream;
use React\Stream\DuplexStreamInterface;

final class Client implements IClient, ISpecialMessage
{
    private const TWITCH_IRC_URI = 'irc.chat.twitch.tv';
    private const TWITCH_IRC_PORT = 6667;
    private const  TWITCH_IRC_MESSAGE_PING = 'PING :tmi.twitch.tv';
    /** @see https://discuss.dev.twitch.tv/t/missing-client-side-message-length-check/21316 */
    private const MAX_LINE = 512;
    private DuplexStreamInterface $socket;
    private LoggerInterface $logger;
    private string $twitchChannel;
    private string $oauthToken;
    private string $botUsername;
    
    public function __construct(string $oauthToken, string $botUsername, string $twitchChannel)
    {
        $this->logger = new NullLogger();
        $this->twitchChannel = $twitchChannel;
        $this->oauthToken = $oauthToken;
        $this->botUsername = $botUsername;
    }
    
    public function connect(LoopInterface $loop): DuplexStreamInterface
    {
        $stream = stream_socket_client(self::TWITCH_IRC_URI . ':' . self::TWITCH_IRC_PORT);
        $this->socket = new DuplexResourceStream($stream, $loop, self::MAX_LINE);
        $this->logger->info(sprintf('Connecting onto %s:%s on twitchChannel %s as %s', self::TWITCH_IRC_URI,
            self::TWITCH_IRC_PORT, $this->twitchChannel, $this->botUsername));
        $this->send(sprintf('PASS %s', $this->oauthToken));
        $this->send(sprintf('NICK %s', $this->botUsername));
        $this->send(sprintf('JOIN #%s', $this->twitchChannel));
        
        return $this->socket;
    }
    
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
    
    public function ping(): void
    {
        $this->send('PING :tmi.twitch.tv');
    }
    
    public function pong(): void
    {
        $this->send('PONG :tmi.twitch.tv');
    }
    
    public function sendMessage(string $message): void
    {
        $this->send(sprintf('PRIVMSG #%s :%s', $this->twitchChannel, $message));
        $this->logger->info('send message ' . $message . " \r\n");
    }
    
    public function emit(string $messageType, array $content): void
    {
        $this->socket->emit($messageType, $content);
    }
    
    public function send(string $message): void
    {
        if (!$this->isConnected()) {
            throw new TwitchConnectionFailedException('Not connected');
        }
        $this->socket->write($message . " \n");
    }
    
    public function parse(string $data)
    {
        /* @phpstan-ignore-next-line */
        $messages = array_filter(preg_split('/[\r\n]/', $data), 'strlen');
        foreach ($messages as $message) {
            if (preg_match_all('/^:(.+?(?=!)).+ PRIVMSG (.+?(?=:)):(.+)$/', $message, $matches)) {
                $message = new Message($matches[3][0], $matches[1][0], trim($matches[2][0]));
                $this->logger->info(sprintf('Message: "%s"', $message));
                $this->socket->emit('message', [
                    json_encode(
                        [
                            'channel' => $message->getChannel(),
                            'nickname' => $message->getNickname(),
                            'message' => $message->getMessage(),
                            'command' => $message->getCommand(),
                            'isCommand' => $message->isCommand(),
                        ]
                    ),
                ]);
            }
            if ($message === self::TWITCH_IRC_MESSAGE_PING) {
                $message = new Message('ping', 'serverbot', 'twitch');
                $this->logger->info(sprintf('Message: "%s"', $message));
                $this->socket->emit('message', [
                    json_encode(
                        [
                            'channel' => $message->getChannel(),
                            'nickname' => $message->getNickname(),
                            'message' => $message->getMessage(),
                            'command' => $message->getCommand(),
                            'isCommand' => $message->isCommand(),
                        ]
                    ),
                ]);
                
            }
        }
    }
    
    public function close()
    {
        $this->socket->close();
    }
    
    public function isConnected(): bool
    {
        return $this->socket->isReadable() && $this->socket->isWritable();
    }
    
    public function get(string $service): IClient
    {
        return $this;
    }
}
