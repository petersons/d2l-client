<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;

final readonly class QuizSpecialAccessAttemptsAllowed implements Arrayable
{
    public function __construct(
        public bool $isUnlimited,
        public int|null $numberOfAttemptsAllowed,
    ) {
        if ($this->numberOfAttemptsAllowed !== null && ($this->numberOfAttemptsAllowed < 1 || $this->numberOfAttemptsAllowed > 10)) {
            throw new \RuntimeException('Number of attempts must be between 1 and 10 inclusive or null for unlimited');
        }
    }

    public function toArray(): array
    {
        return [
            'IsUnlimited' => $this->isUnlimited,
            'NumberOfAttemptsAllowed' => $this->numberOfAttemptsAllowed,
        ];
    }
}
