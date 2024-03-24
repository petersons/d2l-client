<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum\Quiz;

use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#term-GRADINGRULE_T
 */
final class QuestionGradingRule
{
    public const ALL_OR_NOTHING = 0;
    public const RIGHT_MINUS_WRONG = 1;
    public const CORRECT_ANSWERS = 2;
    public const EQUALLY_WEIGHTED_OR_CORRECT_ANSWERS_LIMITED_SELECTIONS = 3;

    public static function make(int $rule): self
    {
        return match ($rule) {
            self::ALL_OR_NOTHING => new self(self::ALL_OR_NOTHING),
            self::RIGHT_MINUS_WRONG => new self(self::RIGHT_MINUS_WRONG),
            self::CORRECT_ANSWERS => new self(self::CORRECT_ANSWERS),
            self::EQUALLY_WEIGHTED_OR_CORRECT_ANSWERS_LIMITED_SELECTIONS => new self(self::EQUALLY_WEIGHTED_OR_CORRECT_ANSWERS_LIMITED_SELECTIONS),
            default => throw new InvalidArgumentException(sprintf('Unknown quiz question grading rule %d', $rule)),
        };
    }

    public function rule(): int
    {
        return $this->rule;
    }

    private function __construct(private int $rule) {}
}
