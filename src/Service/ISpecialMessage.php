<?php
declare(strict_types=1);

namespace App\Service;

interface ISpecialMessage
{
    public function ping(): void;
    
    public function pong(): void;
}
