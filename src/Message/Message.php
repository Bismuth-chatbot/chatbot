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
