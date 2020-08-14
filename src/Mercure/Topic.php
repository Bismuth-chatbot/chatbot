<?php

declare(strict_types=1);

namespace App\Mercure;

class Topic
{
    public static function create(array $params): string
    {
        $uri = 'https://twitch.tv/<channel>';
        if (isset($params['<command>'])) {
            $uri = $uri.'/command/<command>';
        }

        return strtr($uri, $params);
    }
}
