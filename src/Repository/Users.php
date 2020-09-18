<?php
declare(strict_types=1);

namespace App\Repository;

use App\Model\User;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\SerializerInterface;

class Users
{
    private $fs;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;
    private const EXT = '.json';
    private string $usersPath;
    private Finder $finder;
    
    public function __construct(SerializerInterface $serializer, string $usersPath)
    {
        $this->finder = (new Finder())->in($usersPath);
        $this->fs = new Filesystem();
        $this->serializer = $serializer;
        $this->usersPath = $usersPath;
    }
    
    public function findAll(): \Generator
    {
        foreach ($this->finder->files() as $file) {
            yield $this->serializer->deserialize($file->getContents(), User::class, 'json');
        }
    }
    
    public function findByUsername(string $username): ?User
    {
        if (!$this->fs->exists($this->resolve($username))) {
            return null;
        }
        
        return $this->serializer->deserialize(file_get_contents($this->resolve($username)), User::class, 'json');
        
    }
    
    public function save(User $user)
    {
        $serialize = $this->serializer->serialize($user, 'json');
        $this->fs->dumpFile($this->resolve($user->getUsername()), $serialize);
    }
    
    private function hashUsername(string $username): string
    {
        return md5($username);
    }
    
    private function resolve(string $username)
    {
        return $this->usersPath . '/' . $this->hashUsername($username) . self::EXT;
    }
}
