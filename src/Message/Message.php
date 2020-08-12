<?php

namespace App\Message;

final class Message extends AbstractMessage
{
    public function isCommand(): bool
    {
        return false;
    }

    public function getCommand(): string
    {
        return '';
    }

    public function getCommandArguments(): array
    {
        return [];
    }
}
