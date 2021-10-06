<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Enrollment;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @link https://docs.valence.desire2learn.com/res/enroll.html?highlight=roleinfo#Enrollment.RoleInfo
 */
final class RoleInfo implements Arrayable
{
    public function __construct(
        private int $id,
        private string $name,
        private ?string $code = null
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function toArray(): array
    {
        return [
            'Id' => $this->id,
            'Code' => $this->code,
            'Name' => $this->name
        ];
    }
}
