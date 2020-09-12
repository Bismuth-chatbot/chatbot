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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Command\CurrentSpotifyTrack;
use App\Command\Dice;
use App\Spotify\Client as SpotifyClient;
use App\Twitch\Client as TwitchClient;
use App\Twitch\Transport as TwitchTransport;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\Mercure\Jwt\StaticJwtProvider;
use Symfony\Component\Mercure\Publisher;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return function (ContainerConfigurator $configurator) {
    $parameters = $configurator->parameters();
    $parameters->set('app.mercure.jwt', $_ENV['MERCURE_JWT_TOKEN'])
        ->set('app.mercure.hub', $_ENV['MERCURE_HUB_URL'])
    ;

    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$mercureHubUrl', '%app.mercure.hub%')
        ->bind('$twitchChannel', '%app.twitch.channel_name%')
        ->bind('$httpHost', '0.0.0.0:8080')
    ;

    $services->load('App\\', '../src/*')
        ->exclude('../src/{DependencyInjection,Entity,Tests,Kernel.php}')
    ;
    // Register every commands
    $services->load('App\\Command\\', '../src/Command/')->tag('console.command');
    $services->set(StaticJwtProvider::class)->arg('$jwt', '%app.mercure.jwt%');
    $services->set(EventSourceHttpClient::class);
    $services->set(Publisher::class)
        ->args([
            '%app.mercure.hub%',
            service(StaticJwtProvider::class),
            service(HttpClientInterface::class),
        ])
    ;
    /*
     * Spotify Client
     */
    $configurator->parameters()
        ->set('app.spotify.oauth_token', $_ENV['SPOTIFY_OAUTH_TOKEN'])
    ;
    $services->get(SpotifyClient::class)
        ->arg('$spotifyToken', '%app.spotify.oauth_token%')
    ;
    /*
     * Twitch settings
     */
    $configurator->parameters()
        ->set('app.twitch.oauth_token', $_ENV['TWITCH_OAUTH_TOKEN'])
        ->set('app.twitch.bot_username', $_ENV['TWITCH_BOT_USERNAME'])
        ->set('app.twitch.channel_name', $_ENV['TWITCH_CHANNEL_NAME'])
    ;
    $services->get(TwitchClient::class)
        ->arg('$oauthToken', '%app.twitch.oauth_token%')
        ->arg('$botUsername', '%app.twitch.bot_username%')
        ->arg('$twitchChannel', '%app.twitch.channel_name%')
        ->call('setLogger', [service('logger')])
    ;

    $services->get(Dice::class)->arg('$transport', service(TwitchTransport::class));
    $services->get(CurrentSpotifyTrack::class)->arg('$transport', service(TwitchTransport::class));
};
