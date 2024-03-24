<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\Enum\Quiz\LateSubmissionOption;

final class LateSubmissionInfo implements Arrayable
{
    public function __construct(
        private LateSubmissionOption $lateSubmissionOption,
        private int|null $lateLimitMinutes
    ) {}

    public function getLateSubmissionOption(): LateSubmissionOption
    {
        return $this->lateSubmissionOption;
    }

    public function getLateLimitMinutes(): int|null
    {
        return $this->lateLimitMinutes;
    }

    public function toArray(): array
    {
        return [
            'LateSubmissionOption' => $this->lateSubmissionOption->getOption(),
            'LateLimitMinutes' => $this->lateLimitMinutes,
        ];
    }
}
