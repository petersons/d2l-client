<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Petersons\D2L\DTO\RichText;
use Petersons\D2L\Enum\Quiz\EnumerationType;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#Question.QuestionInfo
 */
final class TrueFalse implements QuestionInfo
{
    public function __construct(
        private int $truePartId,
        private float $trueWeight,
        private RichText $trueFeedback,
        private int $falsePartId,
        private float $falseWeight,
        private RichText $falseFeedback,
        private EnumerationType $enumeration,
    ) {}

    public function getTruePartId(): int
    {
        return $this->truePartId;
    }

    public function getTrueWeight(): float
    {
        return $this->trueWeight;
    }

    public function getTrueFeedback(): RichText
    {
        return $this->trueFeedback;
    }

    public function getFalsePartId(): int
    {
        return $this->falsePartId;
    }

    public function getFalseWeight(): float
    {
        return $this->falseWeight;
    }

    public function getFalseFeedback(): RichText
    {
        return $this->falseFeedback;
    }

    public function getEnumeration(): EnumerationType
    {
        return $this->enumeration;
    }

    public function toArray(): array
    {
        return [
            'TruePartId' => $this->truePartId,
            'TrueWeight' => $this->trueWeight,
            'TrueFeedback' => $this->trueFeedback->toArray(),
            'FalsePartId' => $this->falsePartId,
            'FalseWeight' => $this->falseWeight,
            'FalseFeedback' => $this->falseFeedback->toArray(),
            'Enumeration' => $this->enumeration->type(),
        ];
    }
}
