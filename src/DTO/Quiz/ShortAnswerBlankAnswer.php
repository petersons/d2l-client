<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;

final class ShortAnswerBlankAnswer implements Arrayable
{
    public function __construct(
        private string $text,
        private int $weight
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function toArray(): array
    {
        return [
            'Text' => $this->text,
            'Weight' => $this->weight,
        ];
    }
}
