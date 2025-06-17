<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\User;

/**
 * @link https://docs.valence.desire2learn.com/res/user.html#User.UpdateUserData
 */
final class UpdateUser
{
    public function __construct(
        private int $lmsUserId,
        private string $orgDefinedId,
        private string $firstName,
        private string|null $middleName,
        private string $lastName,
        private string|null $externalEmail,
        private string $userName,
        private bool $isActive,
    ) {}

    public function getLmsUserId(): int
    {
        return $this->lmsUserId;
    }

    public function getOrgDefinedId(): string
    {
        return $this->orgDefinedId;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getMiddleName(): string|null
    {
        return $this->middleName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getExternalEmail(): string|null
    {
        return $this->externalEmail;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function toArray(): array
    {
        return [
            'OrgDefinedId' => $this->getOrgDefinedId(),
            'FirstName' => $this->getFirstName(),
            'MiddleName' => $this->getMiddleName(),
            'LastName' => $this->getLastName(),
            'ExternalEmail' => $this->getExternalEmail(),
            'UserName' => $this->getUserName(),
            'Activation' => [
                'IsActive' => $this->isActive(),
            ],
        ];
    }
}
