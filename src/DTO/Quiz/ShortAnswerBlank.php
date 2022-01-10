<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Petersons\D2L\Enum\Quiz\EvaluationType;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#Question.QuestionInfo
 */
final class ShortAnswerBlank implements Arrayable
{
    public function __construct(
        private int $partId,
        private Collection $answers,
        private EvaluationType $evaluationType,
    ) {
    }

    public function getPartId(): int
    {
        return $this->partId;
    }

    /**
     * @return Collection|ShortAnswerBlankAnswer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function getEvaluationType(): EvaluationType
    {
        return $this->evaluationType;
    }

    public function toArray(): array
    {
        return [
            'PartId' => $this->partId,
            'Answers' => $this->answers->toArray(),
            'EvaluationType' => $this->evaluationType->type(),
        ];
    }
}
