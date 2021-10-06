<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\User;

use Carbon\CarbonImmutable;

/**
 * @link https://docs.valence.desire2learn.com/res/user.html#User.UserData
 */
final class UserData
{
    public function __construct(
        private int $orgId,
        private int $userId,
        private string $firstName,
        private ?string $middleName,
        private string $lastName,
        private string $username,
        private ?string $externalEmail,
        private ?string $orgDefinedId,
        private string $uniqueIdentifier,
        private bool $isActive,
        private ?CarbonImmutable $lastAccessedAt
    ) {
    }

    public function getOrgId(): int
    {
        return $this->orgId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getExternalEmail(): ?string
    {
        return $this->externalEmail;
    }

    public function getOrgDefinedId(): ?string
    {
        return $this->orgDefinedId;
    }

    public function getUniqueIdentifier(): string
    {
        return $this->uniqueIdentifier;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getLastAccessedAt(): ?CarbonImmutable
    {
        return $this->lastAccessedAt;
    }
}
