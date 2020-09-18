<?php
declare(strict_types=1);

namespace App\Model;

class User
{
    private string $username;
    private int $score;
    
    public function __construct(string $username, int $score)
    {
        $this->score = $score;
        $this->username = $username;
    }
    
    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }
    
    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }
    
    public function setScore(int $score): void
    {
        $this->score = $score;
    }
}
