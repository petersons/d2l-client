<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum\Quiz;

use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#term-EVALULATION_T
 */
final class EvaluationType
{
    public const CASE_INSENSITIVE = 0;
    public const CASE_SENSITIVE = 1;
    public const REGULAR_EXPRESSION = 2;

    public static function make(int $type): self
    {
        return match ($type) {
            self::CASE_INSENSITIVE => new self(self::CASE_INSENSITIVE),
            self::CASE_SENSITIVE => new self(self::CASE_SENSITIVE),
            self::REGULAR_EXPRESSION => new self(self::REGULAR_EXPRESSION),
            default => throw new InvalidArgumentException(sprintf('Unknown quiz evaluation type %d', $type)),
        };
    }

    public function type(): int
    {
        return $this->type;
    }

    private function __construct(private int $type) {}
}
