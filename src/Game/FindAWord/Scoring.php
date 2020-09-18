<?php
declare(strict_types=1);

namespace App\Game\FindAWord;

class Scoring
{
    public static function rateWord(string $word): int
    {
        $length = strlen($word);
        $score = 0;

        // swap all to lower case
        $word = strtolower($word);
        for ($i = 0; $i < $length; $i++) {
            switch ($word[$i]) {
                case 'e':
                case 'a':
                case 'i':
                case 'o':
                case 'n':
                case 'r':
                case 't':
                case 'l':
                case 's':
                case 'u':
                    $score += 1;
                    break;
                case 'd':
                case 'g':
                    $score += 2;
                    break;
                case 'b':
                case 'c':
                case 'm':
                case 'p':
                    $score += 3;
                    break;
                case 'f':
                case 'h':
                case 'v':
                case 'w':
                case 'y':
                    $score += 4;
                    break;
                case 'k':
                    $score += 5;
                    break;
                case 'j':
                case 'x':
                    $score += 8;
                    break;
                case 'q':
                case 'z':
                    $score += 10;
                    break;
                default:
                
            }
        }
        
        return $score;
    }
}
