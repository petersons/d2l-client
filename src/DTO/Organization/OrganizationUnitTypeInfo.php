<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Organization;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @link https://docs.valence.desire2learn.com/res/orgunit.html#OrgUnit.OrgUnitTypeInfo
 */
final class OrganizationUnitTypeInfo implements Arrayable
{
    public function __construct(
        private int $id,
        private string $code,
        private string $name
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function toArray()
    {
        return [
            'Id' => $this->id,
            'Code' => $this->code,
            'Name' => $this->code
        ];
    }
}
