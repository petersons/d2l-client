<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum\Quiz;

use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#term-ENUMERATION_T
 */
final class EnumerationType
{
    public const NUMBERS = 1;
    public const ROMAN_NUMERALS = 2;
    public const UPPER_CASE_ROMAN_NUMERALS = 3;
    public const LETTERS = 4;
    public const UPPER_CASE_LETTERS = 5;
    public const NO_ENUMERATION = 6;

    public static function make(int $type): self
    {
        return match ($type) {
            self::NUMBERS => new self(self::NUMBERS),
            self::ROMAN_NUMERALS => new self(self::ROMAN_NUMERALS),
            self::UPPER_CASE_ROMAN_NUMERALS => new self(self::UPPER_CASE_ROMAN_NUMERALS),
            self::LETTERS => new self(self::LETTERS),
            self::UPPER_CASE_LETTERS => new self(self::UPPER_CASE_LETTERS),
            self::NO_ENUMERATION => new self(self::NO_ENUMERATION),
            default => throw new InvalidArgumentException(sprintf('Unknown quiz enumeration type %d', $type)),
        };
    }

    public function type(): int
    {
        return $this->type;
    }

    private function __construct(private int $type)
    {
    }
}
