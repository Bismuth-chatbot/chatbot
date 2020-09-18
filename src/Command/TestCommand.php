<?php
declare(strict_types=1);

namespace App\Command;

use App\Repository\Users as UsersRepository;
use App\Twitch\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected static $defaultName = 'app:test';
    /**
     * @var UsersRepository
     */
    private UsersRepository $users;
    /**
     * @var Client
     */
    private Client $client;
    
    public function __construct(Client $client)
    {
        parent::__construct(null);
        $this->client = $client;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->client->ping();
        
        return Command::SUCCESS;
    }
}
