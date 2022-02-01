<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Support\Collection;
use Petersons\D2L\Enum\Quiz\QuestionScaleOption;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#Question.QuestionInfo
 */
final class Likert implements QuestionInfo
{
    public function __construct(
        private QuestionScaleOption $scale,
        private bool $naOption,
        private Collection $statements,
    ) {
    }

    public function getScale(): QuestionScaleOption
    {
        return $this->scale;
    }

    public function isNaOption(): bool
    {
        return $this->naOption;
    }

    /**
     * @return Collection|LikertStatement[]
     */
    public function getStatements(): Collection
    {
        return $this->statements;
    }

    public function toArray(): array
    {
        return [
            'Scale' => $this->scale->getOption(),
            'NaOption' => $this->naOption,
            'Statements' => $this->statements->toArray(),
        ];
    }
}
