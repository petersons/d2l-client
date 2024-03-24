<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;

final class RestrictIPAddressRange implements Arrayable
{
    public function __construct(
        private string $IPRangeStart,
        private string|null $IPRangeEnd
    ) {}

    public function getIPRangeStart(): string
    {
        return $this->IPRangeStart;
    }

    public function getIPRangeEnd(): string|null
    {
        return $this->IPRangeEnd;
    }

    public function toArray(): array
    {
        return [
            'IPRangeStart' => $this->IPRangeStart,
            'IPRangeEnd' => $this->IPRangeEnd,
        ];
    }
}
