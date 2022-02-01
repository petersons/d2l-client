<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;

final class AttemptsAllowed implements Arrayable
{
    public function __construct(
        private bool $isUnlimited,
        private ?int $numberOfAttemptsAllowed
    ) {
    }

    public function isUnlimited(): bool
    {
        return $this->isUnlimited;
    }

    public function getNumberOfAttemptsAllowed(): ?int
    {
        return $this->numberOfAttemptsAllowed;
    }

    public function toArray(): array
    {
        return [
            'IsUnlimited' => $this->isUnlimited,
            'NumberOfAttemptsAllowed' => $this->numberOfAttemptsAllowed,
        ];
    }
}
