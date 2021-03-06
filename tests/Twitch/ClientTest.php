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

namespace App\Tests\Twitch;

use App\Message\AbstractMessage;
use App\Twitch\Client;
use App\Twitch\Message as TwitchMessage;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @dataProvider messageProvider
     */
    public function testMessageParse($input, $result)
    {
        $client = new Client('token', 'username', 'client');

        foreach ($client->parse($input) as $message) {
            $this->assertInstanceOf(AbstractMessage::class, $message);
            $this->assertEquals($message->getMessage(), $result->getMessage());
            $this->assertEquals($message->isCommand(), $result->isCommand());
            $this->assertEquals($message->getNickname(), $result->getNickname());
            $this->assertEquals($message->getChannel(), $result->getChannel());
        }
    }

    public function messageProvider()
    {
        return [
            [':s0yuk4! PRIVMSG s0yuk4:!dice', new TwitchMessage('!dice', 's0yuk4', 's0yuk4')],
            [':s0yuk4! PRIVMSG s0yuk4:hello world', new TwitchMessage('hello world', 's0yuk4', 's0yuk4')],
            [':s0yuk4! PRIVMSG s0yuk4:jé soui français', new TwitchMessage('jé soui français', 's0yuk4', 's0yuk4')],
            [':s0yuk4! PRIVMSG s0yuk4:éééééééé', new TwitchMessage('éééééééé', 's0yuk4', 's0yuk4')],
            [':s0yuk4! PRIVMSG s0yuk4:!éééé', new TwitchMessage('!éééé', 's0yuk4', 's0yuk4')],
        ];
    }
}
