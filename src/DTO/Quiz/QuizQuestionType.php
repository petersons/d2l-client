<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#term-QUESTION_T
 */
final class QuizQuestionType
{
    public const MULTIPLE_CHOICE = 1;
    public const TRUE_FALSE = 2;
    public const FILL_IN_THE_BLANK = 3;
    public const MULTI_SELECT = 4;
    public const MATCHING = 5;
    public const ORDERING = 6;
    public const LONG_ANSWER = 7;
    public const SHORT_ANSWER = 8;
    public const LIKERT = 9;
    public const IMAGE_INFO = 10;
    public const TEXT_INFO = 11;
    public const ARITHMETIC = 12;
    public const SIGNIFICANT_FIGURES = 13;
    public const MULTI_SHORT_ANSWER = 14;

    private const SUPPORTED_TYPES = [
        self::MULTIPLE_CHOICE,
        self::TRUE_FALSE,
        self::FILL_IN_THE_BLANK,
        self::MULTI_SELECT,
        self::MATCHING,
        self::ORDERING,
        self::LONG_ANSWER,
        self::SHORT_ANSWER,
        self::LIKERT,
        self::IMAGE_INFO,
        self::TEXT_INFO,
        self::ARITHMETIC,
        self::SIGNIFICANT_FIGURES,
        self::MULTI_SHORT_ANSWER,
    ];

    public static function make(int $type): self
    {
        return new self($type);
    }

    public function type(): int
    {
        return $this->type;
    }

    public function isMultipleChoice(): bool
    {
        return self::MULTIPLE_CHOICE === $this->type;
    }

    public function isTrueFalse(): bool
    {
        return self::TRUE_FALSE === $this->type;
    }

    public function isFillInTheBlank(): bool
    {
        return self::FILL_IN_THE_BLANK === $this->type;
    }

    public function isMultiSelect(): bool
    {
        return self::MULTI_SELECT === $this->type;
    }

    public function isMatching(): bool
    {
        return self::MATCHING === $this->type;
    }

    public function isOrdering(): bool
    {
        return self::ORDERING === $this->type;
    }

    public function isLongAnswer(): bool
    {
        return self::LONG_ANSWER === $this->type;
    }

    public function isShortAnswer(): bool
    {
        return self::SHORT_ANSWER === $this->type;
    }

    public function isLikert(): bool
    {
        return self::LIKERT === $this->type;
    }

    public function isImageInfo(): bool
    {
        return self::IMAGE_INFO === $this->type;
    }

    public function isTextInfo(): bool
    {
        return self::TEXT_INFO === $this->type;
    }

    public function isArithmetic(): bool
    {
        return self::ARITHMETIC === $this->type;
    }

    public function isSignificantFigures(): bool
    {
        return self::SIGNIFICANT_FIGURES === $this->type;
    }

    public function isMultiShortAnswer(): bool
    {
        return self::MULTI_SHORT_ANSWER === $this->type;
    }

    private function __construct(private int $type)
    {
        if (!in_array($type, self::SUPPORTED_TYPES, true)) {
            throw new InvalidArgumentException(sprintf('Invalid type "%d" given.', $type));
        }
    }
}
