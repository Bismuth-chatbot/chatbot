<?php
declare(strict_types=1);

namespace App\Drift\Controller\Twitch;

use App\Drift\Controller\CommandController;
use App\Service\IClient;
use Psr\Http\Message\RequestInterface;
use React\Http\Message\Response;

class PostCommand implements CommandController
{
    public function __invoke(IClient $client, RequestInterface $request): Response
    {
     
        $body = json_decode($request->getBody()->getContents(), true);
        $client->sendMessage($body['message']);
        
        return new Response(204);
    }
}
