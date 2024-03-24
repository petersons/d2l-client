<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\User;

/**
 * @link https://docs.valence.desire2learn.com/res/user.html#User.CreateUserData
 */
final class CreateUser
{
    public function __construct(
        private string $orgDefinedId,
        private string $firstName,
        private string|null $middleName,
        private string $lastName,
        private string|null $externalEmail,
        private string $username,
        private int $roleId,
        private bool $isActive,
        private bool $sendCreationEmail
    ) {}

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

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isSendCreationEmail(): bool
    {
        return $this->sendCreationEmail;
    }

    public function toArray(): array
    {
        return [
            'OrgDefinedId' => $this->getOrgDefinedId(),
            'FirstName' => $this->getFirstName(),
            'MiddleName' => $this->getMiddleName(),
            'LastName' => $this->getLastName(),
            'ExternalEmail' => $this->getExternalEmail(),
            'UserName' => $this->getUsername(),
            'RoleId' => $this->getRoleId(),
            'IsActive' => $this->isActive(),
            'SendCreationEmail' => $this->isSendCreationEmail(),
        ];
    }
}
