<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Support\Collection;
use Petersons\D2L\Enum\Quiz\EnumerationType;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#Question.QuestionInfo
 */
final class MultipleChoiceAnswers implements QuestionInfo
{
    public function __construct(
        private Collection $answers,
        private bool $randomize,
        private EnumerationType $enumeration,
    ) {}

    /**
     * @return Collection<MultipleChoiceAnswer>
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

    public function toArray(): array
    {
        return [
            'Answers' => $this->answers->toArray(),
            'Randomize' => $this->randomize,
            'Enumeration' => $this->enumeration->type(),
        ];
    }
}
