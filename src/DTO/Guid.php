<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO;

final class Guid
{
    public function __construct(private string $value) {}

    public function getValue(): string
    {
        return $this->value;
    }
}
