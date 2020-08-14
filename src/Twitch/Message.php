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

use App\Message\AbstractMessage;

final class Message extends AbstractMessage
{
    public function isCommand(): bool
    {
        return 0 === strpos($this->message, '!');
    }

    public function getCommand(): string
    {
        return explode(' ', substr($this->message, 1))[0];
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + ['isCommand' => $this->isCommand(), 'command' => $this->getCommand()];
    }

    public function getCommandArguments(): array
    {
        $command = explode(' ', substr($this->message, 1));

        return array_splice($command, 1);
    }
}
