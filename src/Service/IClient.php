<?php
declare(strict_types=1);

namespace App\Service;

interface IClient
{
    public function sendMessage(string $message): void;
    
    public function emit(string $messageType, array $content): void;
    
    public function get(string $service): IClient;
    
}
