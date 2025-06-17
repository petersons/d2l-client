<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\User;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @link https://docs.valence.desire2learn.com/res/user.html#User.User
 */
final class User implements Arrayable
{
    public function __construct(
        private string|null $identifier = null,
        private string|null $displayName = null,
        private string|null $emailAddress = null,
        private string|null $orgDefinedId = null,
        private string|null $profileBadgeUrl = null,
        private string|null $profileIdentifier = null,
    ) {}

    public function getIdentifier(): string|null
    {
        return $this->identifier;
    }

    public function getDisplayName(): string|null
    {
        return $this->displayName;
    }

    public function getEmailAddress(): string|null
    {
        return $this->emailAddress;
    }

    public function getOrgDefinedId(): string|null
    {
        return $this->orgDefinedId;
    }

    public function getProfileBadgeUrl(): string|null
    {
        return $this->profileBadgeUrl;
    }

    public function getProfileIdentifier(): string|null
    {
        return $this->profileIdentifier;
    }

    public function toArray(): array
    {
        return [
            'Identifier' => $this->identifier,
            'DisplayName' => $this->displayName,
            'EmailAddress' => $this->emailAddress,
            'OrgDefinedId' => $this->orgDefinedId,
            'ProfileBadgeUrl' => $this->profileBadgeUrl,
            'ProfileIdentifier' => $this->profileIdentifier,
        ];
    }
}
