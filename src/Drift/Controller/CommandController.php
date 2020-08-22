<?php
declare(strict_types=1);

namespace App\Drift\Controller;

use App\Service\IClient;
use Psr\Http\Message\RequestInterface;
use React\Http\Message\Response;

interface CommandController
{
    public function __invoke(IClient $client, RequestInterface $request): Response;
}
