<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\DTO\RichText;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#Question.QuestionInfo
 */
final class MultipleChoiceAnswer implements Arrayable
{
    public function __construct(
        private int $partId,
        private RichText $answer,
        private RichText $answerFeedback,
        private int $weight,
    ) {
    }

    public function getPartId(): int
    {
        return $this->partId;
    }

    public function getAnswer(): RichText
    {
        return $this->answer;
    }

    public function getAnswerFeedback(): RichText
    {
        return $this->answerFeedback;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function toArray(): array
    {
        return [
            'PartId' => $this->partId,
            'Answer' => $this->answer->toArray(),
            'AnswerFeedback' => $this->answerFeedback->toArray(),
            'Weight' => $this->weight,
        ];
    }
}
