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

namespace App\Command;

use App\Http\Client as HttpClient;
use App\Mercure\Consumer as MercureConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Dice extends Command
{
    private MercureConsumer $mercureConsumer;
    private HttpClient $httpClient;
    private string $twitchChannel;

    public function __construct(HttpClient $httpClient, MercureConsumer $mercureConsumer, string $twitchChannel)
    {
        $this->mercureConsumer = $mercureConsumer;
        $this->httpClient = $httpClient;
        parent::__construct();
        $this->twitchChannel = $twitchChannel;
    }

    protected static $defaultName = 'app:dice';

    protected function configure()
    {
        $this
            ->setDescription('Rolls a dice and answer to twitch when requested.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $topics = [sprintf('https://twitch.tv/%s/command/dice', $this->twitchChannel)];
        foreach ($this->mercureConsumer->__invoke($topics) as $message) {
            $dice = $message->getCommandArguments()[0] ?? 6;
            $rand = random_int(1, $dice);
            $this->httpClient->postMessage('twitch', sprintf('%s sent a %d dice resulting in a %d',
                '@'.$message->getNickname(),
                $dice,
                $rand
            ));
        }

        return Command::SUCCESS;
    }
}
