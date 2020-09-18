<?php
declare(strict_types=1);

namespace App\Command;

use App\Game\FindAWord\Scoring;
use App\Http\Client as HttpClient;
use App\Mercure\Consumer as MercureConsumer;
use App\Model\User;
use App\Repository\Users as UsersRepository;
use App\Twitch\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class GameFindWord extends Command
{
    protected static $defaultName = 'app:game:find:word';
    private string $twitchChannel;
    private string $pathOfFile;
    /**
     * @var MercureConsumer
     */
    private MercureConsumer $consumer;
    /**
     * @var HttpClient
     */
    private HttpClient $client;
    /**
     * @var UsersRepository
     */
    private UsersRepository $users;
    
    public function __construct(
        string $twitchChannel,
        MercureConsumer $consumer,
        HttpClient $client,
        UsersRepository $users
    ) {
        
        $this->twitchChannel = $twitchChannel;
        $this->consumer = $consumer;
        $this->pathOfFile = getcwd() . '/games/findword.txt';
        parent::__construct(null);
        $this->client = $client;
        $this->users = $users;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $topics = [sprintf('https://twitch.tv/%s/command/findword', $this->twitchChannel)];
        /** @var Message $message */
        $searchingText = $this->findAWord($fs);
        $output->writeln('is running');
        foreach ($this->consumer->__invoke($topics) as $message) {
            
            $output->writeln($message->getMessage());
            foreach (explode(' ', $message->getMessage()) as $word) {
                
                if (trim($word) === trim($searchingText)) {
                    
                    $this->client->postMessage('twitch',
                        sprintf(
                            'Félicitation %s tu as trouvé le mot caché qui éte "%s" ',
                            $message->getNickname(),
                            $searchingText
                        ));
                    $fs->remove($this->pathOfFile);
                    $searchingText = $this->findAWord($fs);
                    $this->client->postUser(new User(
                        $message->getNickname(),
                        Scoring::rateWord(trim($word))
                    ));
                    $scores = '';
                    /** @var User $user */
                    foreach ($this->users->findAll() as $user) {
                        $scores .= '• ' . $user->getUsername() . " : " . $user->getScore() . "\n";
                    }
                    $this->client->postMessage('twitch', 'Petit rappel des scores :');
                    $this->client->postMessage('twitch', $scores);
                    
                }
            }
            
        }
        
        return Command::SUCCESS;
        
    }
    
    private function findAWord(Filesystem $fs): string
    {
        if (!$fs->exists($this->pathOfFile)) {
            $listOfWord = explode("\n",
                file_get_contents('https://raw.githubusercontent.com/hbenbel/French-Dictionary/master/dictionary/dictionary.txt'));
            $searchingText = $listOfWord[random_int(0, count($listOfWord) - 1)];
            $fs->dumpFile($this->pathOfFile, $searchingText);
        }
        
        return file_get_contents($this->pathOfFile);
    }
}
