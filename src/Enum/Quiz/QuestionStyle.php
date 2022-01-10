<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum\Quiz;

use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#term-STYLE_T
 */
final class QuestionStyle
{
    public const HORIZONTAL = 1;
    public const VERTICAL = 2;
    public const DROPDOWN = 3;

    public static function make(int $style): self
    {
        return match ($style) {
            self::HORIZONTAL => new self(self::HORIZONTAL),
            self::VERTICAL => new self(self::VERTICAL),
            self::DROPDOWN => new self(self::DROPDOWN),
            default => throw new InvalidArgumentException(sprintf('Unknown quiz question style type %d', $style)),
        };
    }

    public function style(): int
    {
        return $this->style;
    }

    private function __construct(private int $style)
    {
    }
}
