<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Organization;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @link https://docs.valence.desire2learn.com/res/orgunit.html#OrgUnit.OrgUnitProperties
 */
final class OrgUnitProperties implements Arrayable
{
    public function __construct(
        private string $identifier,
        private string $name,
        private OrganizationUnitTypeInfo $organizationUnitTypeInfo,
        private string $path,
        private string|null $code = null
    ) {}

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOrganizationUnitTypeInfo(): OrganizationUnitTypeInfo
    {
        return $this->organizationUnitTypeInfo;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getCode(): string|null
    {
        return $this->code;
    }

    public function toArray(): array
    {
        return [
            'Identifier' => $this->identifier,
            'Name' => $this->name,
            'Code' => $this->code,
            'Path' => $this->path,
            'Type' => $this->organizationUnitTypeInfo->toArray()
        ];
    }
}
