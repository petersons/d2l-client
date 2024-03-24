<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum\Quiz;

use InvalidArgumentException;

final class QuestionScaleOption
{
    public const ONE_TO_FIVE = 0;
    public const ONE_TO_EIGHT = 1;
    public const ONE_TO_TEN = 2;
    public const AGREEMENT = 3;
    public const SATISFACTION = 4;
    public const FREQUENCY = 5;
    public const IMPORTANCE = 6;
    public const OPPOSITION = 7;

    public static function make(int $option): self
    {
        return match ($option) {
            self::ONE_TO_FIVE => new self(self::ONE_TO_FIVE),
            self::ONE_TO_EIGHT => new self(self::ONE_TO_EIGHT),
            self::ONE_TO_TEN => new self(self::ONE_TO_TEN),
            self::AGREEMENT => new self(self::AGREEMENT),
            self::SATISFACTION => new self(self::SATISFACTION),
            self::FREQUENCY => new self(self::FREQUENCY),
            self::IMPORTANCE => new self(self::IMPORTANCE),
            self::OPPOSITION => new self(self::OPPOSITION),
            default => throw new InvalidArgumentException(sprintf('Unknown question scale option %d', $option)),
        };
    }

    public function getOption(): int
    {
        return $this->option;
    }

    private function __construct(private int $option) {}
}
