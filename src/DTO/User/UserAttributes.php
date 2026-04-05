<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\User;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @link https://docs.valence.desire2learn.com/res/user.html#Attributes.UserAttributes
 */
final readonly class UserAttributes implements Arrayable
{
    /**
     * @param list<UserAttribute> $attributes
     */
    public function __construct(
        public int $userId,
        public array $attributes,
    ) {}

    public function toArray(): array
    {
        $attributes = [];

        foreach ($this->attributes as $attribute) {
            $attributes[] = $attribute->toArray();
        }

        return [
            'UserId' => $this->userId,
            'Attributes' => $attributes,
        ];
    }
}
