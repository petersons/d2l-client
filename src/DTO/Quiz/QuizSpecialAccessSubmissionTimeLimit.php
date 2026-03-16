<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;

final readonly class QuizSpecialAccessSubmissionTimeLimit implements Arrayable
{
    public function __construct(
        public bool $isEnforced,
        public int $timeLimitValue,
    ) {
        if ($this->timeLimitValue < 0 || $this->timeLimitValue > 9999) {
            throw new \RuntimeException('Time limit must be between 0 and 9999 inclusive');
        }
    }

    public function toArray(): array
    {
        return [
            'IsEnforced' => $this->isEnforced,
            'TimeLimitValue' => $this->timeLimitValue,
        ];
    }
}
