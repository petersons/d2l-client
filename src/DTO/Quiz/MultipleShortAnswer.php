<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\Enum\Quiz\EvaluationType;

final class MultipleShortAnswer implements Arrayable
{
    public function __construct(
        private string $answerText,
        private float $weight,
        private EvaluationType $evaluationType,
    ) {
    }

    public function getAnswerText(): string
    {
        return $this->answerText;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function getEvaluationType(): EvaluationType
    {
        return $this->evaluationType;
    }

    public function toArray(): array
    {
        return [
            'AnswerText' => $this->answerText,
            'Weight' => $this->weight,
            'EvaluationType' => $this->evaluationType->type(),
        ];
    }
}
