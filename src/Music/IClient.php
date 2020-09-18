<?php
declare(strict_types=1);

namespace App\Music;

interface IClient
{
    public function getCurrentTrack();
    
    public function get(string $service): IClient;
}
