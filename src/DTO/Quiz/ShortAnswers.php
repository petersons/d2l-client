<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Support\Collection;
use Petersons\D2L\Enum\Quiz\QuestionGradingRule;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#Question.QuestionInfo
 */
final class ShortAnswers implements QuestionInfo
{
    public function __construct(
        private Collection $blanks,
        private QuestionGradingRule $gradingType,
    ) {
    }

    /**
     * @return Collection|ShortAnswerBlank[]
     */
    public function getBlanks(): Collection
    {
        return $this->blanks;
    }

    public function getGradingType(): QuestionGradingRule
    {
        return $this->gradingType;
    }

    public function toArray(): array
    {
        return [
            'Blanks' => $this->blanks->toArray(),
            'GradingType' => $this->gradingType->rule(),
        ];
    }
}
