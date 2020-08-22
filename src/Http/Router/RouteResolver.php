<?php
declare(strict_types=1);

namespace App\Http\Router;

use function Symfony\Component\String\u;

class RouteResolver
{
    private const SUFFIX_COMMAND_CONTROLLER = 'Command';
    
    public static function resolve(string $verb, string $path): string
    {
        return (string)u($path)->slice(1)->lower()->camel()->title() . '\\' . u($verb)->lower()->camel()->title() . self::SUFFIX_COMMAND_CONTROLLER;
    }
}
