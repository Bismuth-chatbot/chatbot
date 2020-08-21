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

namespace App\Http;

use React\EventLoop\LoopInterface;
use React\Http\Server as HttpServer;
use React\Socket\Server as ServerSocket;

final class Server
{
    private string $httpHost;

    public function __construct(string $httpHost)
    {
        $this->httpHost = $httpHost;
    }

    public function run(LoopInterface $loop, callable $func)
    {
        $server = new HttpServer($loop, $func);
        $server->listen(new ServerSocket('tcp://'.$this->httpHost, $loop));
    }

    public function getHttpHost()
    {
        return $this->httpHost;
    }
}
