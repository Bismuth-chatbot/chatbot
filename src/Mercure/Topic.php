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
