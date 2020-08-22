<?php
declare(strict_types=1);

namespace App\Drift\Controller\Twitch;

use App\Drift\Controller\CommandController;
use App\Service\IClient;
use Psr\Http\Message\RequestInterface;
use React\Http\Message\Response;

class GetCommand implements CommandController
{
    public function __invoke(IClient $client, RequestInterface $request): Response
    {
        $query = (array)$request->getQueryParams();
        $client->emit('message', [json_encode($query)]);
        
        return new Response(200);
    }
}
