<?php
declare(strict_types=1);

namespace App\Drift\Controller\User;

use App\Drift\Controller\CommandController;
use App\Model\User;
use App\Repository\Users as UsersRepository;
use App\Service\IClient;
use Psr\Http\Message\RequestInterface;
use React\Http\Message\Response;
use Symfony\Component\Serializer\SerializerInterface;

class PostCommand implements CommandController
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;
    /**
     * @var UsersRepository
     */
    private UsersRepository $users;
    
    public function __construct(SerializerInterface $serializer, UsersRepository $users)
    {
        
        $this->serializer = $serializer;
        $this->users = $users;
    }
    
    public function __invoke(IClient $client, RequestInterface $request): Response
    {
        
        /** @var User $user */
        $user = $this->serializer->deserialize($request->getBody()->getContents(), User::class, 'json');
        $userDB = $this->users->findByUsername($user->getUsername());
        if ($userDB instanceof User) {
            $user->setScore((int)$user->getScore() + $userDB->getScore());
        }
        $this->users->save($user);
        
        return new Response(201, [], "ok");
    }
}
