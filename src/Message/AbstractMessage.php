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

namespace App\Message;

abstract class AbstractMessage implements MessageInterface
{
    protected string $message;
    protected string $nickname;
    protected string $channel;

    public function __construct(string $message, string $nickname, string $channel)
    {
        $this->message = $message;
        $this->nickname = $nickname;
        $this->channel = $channel;
    }

    abstract public function isCommand(): bool;

    abstract public function getCommand(): string;

    abstract public function getCommandArguments(): array;

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function __toString()
    {
        return sprintf('Message on "%s" from "%s": %s', $this->channel, $this->nickname, $this->message);
    }
}
