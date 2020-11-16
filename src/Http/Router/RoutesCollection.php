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

namespace App\Http\Router;

use App\Drift\Controller\CommandController;
use App\Http\Router\Exception\RouteNotFoundException;
use function Symfony\Component\String\u;

class RoutesCollection
{
    private iterable  $routes;

    public function __construct(iterable $routes)
    {
        $this->routes = $routes;
    }

    public function get(string $verb, string $path): CommandController
    {
        $resolver = RouteResolver::resolve($verb, $path);
        foreach ($this->routes as $route) {
            if (null !== u(get_class($route))->indexOf($resolver)) {
                return $route;
            }
        }
        throw new RouteNotFoundException();
    }
}
