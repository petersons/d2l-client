<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\Enum\Quiz\EvaluationType;

final class FillInTheBlankAnswer implements Arrayable
{
    public function __construct(
        private string $textAnswer,
        private float $weight,
        private EvaluationType $evaluationType
    ) {}

    public function getTextAnswer(): string
    {
        return $this->textAnswer;
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
            'TextAnswer' => $this->textAnswer,
            'Weight' => $this->weight,
            'EvaluationType' => $this->evaluationType->type(),
        ];
    }
}
