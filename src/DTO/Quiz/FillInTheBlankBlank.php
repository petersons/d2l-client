<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

final class FillInTheBlankBlank implements Arrayable
{
    public function __construct(
        private int $partId,
        private int $size,
        private Collection $answers
    ) {
    }

    public function getPartId(): int
    {
        return $this->partId;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return Collection|FillInTheBlankAnswer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function toArray(): array
    {
        return [
            'PartId' => $this->partId,
            'Size' => $this->size,
            'Answers' => $this->answers->toArray(),
        ];
    }
}
