<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Organization;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @link https://docs.valence.desire2learn.com/res/orgunit.html#Org.Organization
 */
final class OrganizationInfo implements Arrayable
{
    public function __construct(
        private string $identifier,
        private string $name,
        private string $timeZone
    ) {}

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    public function toArray(): array
    {
        return [
            'Identifier' => $this->identifier,
            'Name' => $this->name,
            'Timezone' => $this->timeZone
        ];
    }
}
