<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum\Quiz;

use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#term-OVERALLGRADECALCULATION_T
 */
final class OverallGradeCalculationOption
{
    public const HIGHEST_ATTEMPT = 1;
    public const LOWEST_ATTEMPT = 2;
    public const AVERAGE_OF_ALL_ATTEMPTS = 3;
    public const FIRST_ATTEMPT = 4;
    public const LAST_ATTEMPT = 5;

    public static function make(int $option): self
    {
        return match ($option) {
            self::HIGHEST_ATTEMPT => new self(self::HIGHEST_ATTEMPT),
            self::LOWEST_ATTEMPT => new self(self::LOWEST_ATTEMPT),
            self::AVERAGE_OF_ALL_ATTEMPTS => new self(self::AVERAGE_OF_ALL_ATTEMPTS),
            self::FIRST_ATTEMPT => new self(self::FIRST_ATTEMPT),
            self::LAST_ATTEMPT => new self(self::LAST_ATTEMPT),
            default => throw new InvalidArgumentException(sprintf('Unknown overall grade calculation option %d', $option)),
        };
    }

    public function getOption(): int
    {
        return $this->option;
    }

    private function __construct(private int $option)
    {
    }
}
