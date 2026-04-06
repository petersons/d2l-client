<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\User;

use Illuminate\Contracts\Support\Arrayable;

final readonly class UserAttribute implements Arrayable
{
    /**
     * @param list<string> $value
     */
    public function __construct(
        public string $attributeId,
        public array $value,
    ) {}

    public function toArray(): array
    {
        return [
            'AttributeId' => $this->attributeId,
            'Value' => $this->value,
        ];
    }
}
