<?php
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
            if (u(get_class($route))->indexOf($resolver) !== null) {
                return $route;
            }
            
        }
        throw new RouteNotFoundException();
    }
}
