<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;

final class SubmissionTimeLimit implements Arrayable
{
    public function __construct(
        private bool $isEnforced,
        private bool $showClock,
        private int $timeLimitValue
    ) {}

    public function isEnforced(): bool
    {
        return $this->isEnforced;
    }

    public function isShowClock(): bool
    {
        return $this->showClock;
    }

    public function getTimeLimitValue(): int
    {
        return $this->timeLimitValue;
    }

    public function toArray(): array
    {
        return [
            'IsEnforced' => $this->isEnforced,
            'ShowClock' => $this->showClock,
            'TimeLimitValue' => $this->timeLimitValue,
        ];
    }
}
