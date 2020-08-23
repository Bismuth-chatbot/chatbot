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

namespace App\Drift\Controller;

use App\Service\IClient;
use Psr\Http\Message\RequestInterface;
use React\Http\Message\Response;

interface CommandController
{
    public function __invoke(IClient $client, RequestInterface $request): Response;
}
