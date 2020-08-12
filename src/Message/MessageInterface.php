<?php

namespace App\Message;

interface MessageInterface
{
    public function isCommand(): bool;

    public function getCommand(): string;

    public function getCommandArguments(): array;

    public function getMessage(): string;

    public function getNickname(): string;

    public function getChannel(): string;

    public function __toString();
}
