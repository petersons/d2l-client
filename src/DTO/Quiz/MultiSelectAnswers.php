<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Support\Collection;
use Petersons\D2L\Enum\Quiz\EnumerationType;
use Petersons\D2L\Enum\Quiz\QuestionGradingRule;
use Petersons\D2L\Enum\Quiz\QuestionStyle;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#Question.QuestionInfo
 */
final class MultiSelectAnswers implements QuestionInfo
{
    public function __construct(
        private Collection $answers,
        private bool $randomize,
        private EnumerationType $enumeration,
        private QuestionStyle $style,
        private QuestionGradingRule $gradingType,
    ) {
    }

    /**
     * @return Collection|MultiSelectAnswer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function isRandomize(): bool
    {
        return $this->randomize;
    }

    public function getEnumeration(): EnumerationType
    {
        return $this->enumeration;
    }

    public function getStyle(): QuestionStyle
    {
        return $this->style;
    }

    public function getGradingType(): QuestionGradingRule
    {
        return $this->gradingType;
    }

    public function toArray(): array
    {
        return [
            'Answers' => $this->answers->toArray(),
            'Randomize' => $this->randomize,
            'Enumeration' => $this->enumeration->type(),
            'Style' => $this->style->style(),
            'GradingType' => $this->gradingType->rule(),
        ];
    }
}
