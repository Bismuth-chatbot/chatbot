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
