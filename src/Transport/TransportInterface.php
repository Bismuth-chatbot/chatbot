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

namespace App\Transport;

use App\Message\MessageInterface;

interface TransportInterface
{
    /**
     * Get received commands.
     *
     * @return \Iterator|MessageInterface[]
     */
    public function commands(string $command): \Iterator;

    /**
     * Send message to target.
     */
    public function send(string $message): void;
}
