<?php
declare(strict_types=1);

namespace App\Analisys;

class Comportment
{
    private array $negativeWord;
    private array $positiveWord;
    private array $negation;
    private string $verdict;
    private int $scorePositivity;
    public const POSITIVE = 'POSITIVE';
    public const NEGATIVE = 'NEGATIVE';
    public const NEUTRAL = 'NEUTRAL';
    
    public function __construct(array $words)
    {
        
        $this->negativeWord = $words['negativeWords'];
        $this->positiveWord = $words['positiveWords'];
        $this->negation = $words['negation'];
        $this->verdict = self::NEUTRAL;
        $this->scorePositivity = 0;
        
    }
    
    public function analysis(string $sentence)
    {
        /*Cleaning the input data*/
        $sentence = preg_replace('/\.+/', '.', $sentence);
        $sentence = preg_replace('/http:\/\/\S+/', '', $sentence);
        /*Calculate the result of the input statement*/
        $this->calculate($sentence);
        
        return $this;
        
    }
    
    public function __toString()
    {
        return (string)$this->verdict;
    }
    
    public function getScorePositivity(): int
    {
        return (int)$this->scorePositivity;
    }
    
    private function calculate(string $sentence): ?string
    {
        /*statement is neutral initially.*/
        $posScore = 0;
        $negScore = 0;
        $negatorScore = 0;
        $verdict = self::NEUTRAL;
        /*If null statement then return*/
        if ($sentence == '' || $sentence == ' ') {
            return null;
        }
        /*If multiple statements, pass each statement*/
        if (strpos($sentence, '.') !== false) {
            $sentences = explode('.', $sentence);
            foreach ($sentences as $eachSentence) {
                if ($eachVerdict = $this->calculate(trim($eachSentence))) {
                    $verdict .= $eachVerdict . ";";
                }
            }
            
            return $this->verdict = $this->avg($verdict);
        }
        if ($sentence != str_ireplace($this->negation, '', $sentence)) {
            $negatorScore++;
        }
        if ($sentence != str_ireplace($this->positiveWord, '', $sentence)) {
            $posScore++;
        }
        if ($sentence != str_ireplace($this->negativeWord, '', $sentence)) {
            $negScore++;
        }
        /*If statement only contains negators then it is negative response.*/
        if ($negatorScore > 0) {
            if ($posScore == 0 && $negScore == 0) {
                return $verdict = self::NEGATIVE;
            } else {
                ($posScore > 0) ?: $posScore = -1;
                ($negScore > 0) ?: $negScore = -1;
            }
        }
        /*If statement has more postive words than its postive else negative*/
        ($posScore > $negScore) ? $verdict = self::POSITIVE : $verdict = self::NEGATIVE;
        /*If statement has no positive/negative words then neutral*/
        if ($posScore === $negScore) {
            $verdict = self::NEUTRAL;
        }
        $this->scorePositivity = $posScore;
        
        return $this->verdict = $verdict;
    }
    
    public function is(string $emotion)
    {
        return $this->verdict === $emotion;
    }
    
    private function avg($verdict): string
    {
        $verdict = explode(';', $verdict);
        $pos = 0;
        $neg = 0;
        $neutral = 0;
        $result = '';
        foreach ($verdict as $sentiment) {
            switch ($sentiment) {
                case 'positive':
                    $pos++;
                    break;
                case 'negative':
                    $neg++;
                    break;
                case 'neutral':
                    $neutral++;
                    break;
                default:
                    break;
            }
        }
        if ($neutral > 0 && $pos == 0 && $neg == 0) {
            return 'neutral';
        }
        if ($pos > $neg) {
            return 'positive';
        }
        if ($neg > $pos) {
            return 'negative';
        }
        if ($neg == $pos) {
            $result = 'neutral';
        }
        
        return $result;
    }
}
