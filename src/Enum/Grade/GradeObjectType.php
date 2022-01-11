<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum\Grade;

use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/grade.html#term-GRADEOBJ_T
 */
final class GradeObjectType
{
    public const NUMBERS = 1;
    public const PASS_FAIL = 2;
    public const SELECT_BOX = 3;
    public const TEXT = 4;
    public const CALCULATED = 5;
    public const FORMULA = 6;
    public const FINAL_CALCULATED = 7;
    public const FINAL_ADJUSTED = 8;
    public const CATEGORY = 9;

    public static function make(int $type): self
    {
        return match ($type) {
            self::NUMBERS => new self(self::NUMBERS),
            self::PASS_FAIL => new self(self::PASS_FAIL),
            self::SELECT_BOX => new self(self::SELECT_BOX),
            self::TEXT => new self(self::TEXT),
            self::CALCULATED => new self(self::CALCULATED),
            self::FORMULA => new self(self::FORMULA),
            self::FINAL_CALCULATED => new self(self::FINAL_CALCULATED),
            self::FINAL_ADJUSTED => new self(self::FINAL_ADJUSTED),
            self::CATEGORY => new self(self::CATEGORY),
            default => throw new InvalidArgumentException(sprintf('Unknown grade object type %d', $type)),
        };
    }

    public function type(): int
    {
        return $this->type;
    }

    public function isNumbers(): bool
    {
        return self::NUMBERS === $this->type;
    }

    public function isPassFail(): bool
    {
        return self::PASS_FAIL === $this->type;
    }

    public function isSelectBox(): bool
    {
        return self::SELECT_BOX === $this->type;
    }

    public function isText(): bool
    {
        return self::TEXT === $this->type;
    }

    public function isCalculated(): bool
    {
        return self::CALCULATED === $this->type;
    }

    public function isFormula(): bool
    {
        return self::FORMULA === $this->type;
    }

    public function isFinalCalculated(): bool
    {
        return self::FINAL_CALCULATED === $this->type;
    }

    public function isFinalAdjusted(): bool
    {
        return self::FINAL_ADJUSTED === $this->type;
    }

    public function isCategory(): bool
    {
        return self::CATEGORY === $this->type;
    }

    private function __construct(private int $type)
    {
    }
}
