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

namespace App\Exception;

class TwitchConnectionFailedException extends \Exception
{
    public function __construct($socketError)
    {
        parent::__construct(sprintf('The connection to the irc chan failed: %s', $socketError));
    }
}
