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
        private ?string $identifier = null,
        private ?string $displayName = null,
        private ?string $emailAddress = null,
        private ?string $orgDefinedId = null,
        private ?string $profileBadgeUrl = null,
        private ?string $profileIdentifier = null
    ) {
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function getOrgDefinedId(): ?string
    {
        return $this->orgDefinedId;
    }

    public function getProfileBadgeUrl(): ?string
    {
        return $this->profileBadgeUrl;
    }

    public function getProfileIdentifier(): ?string
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
            'ProfileIdentifier' => $this->profileIdentifier
        ];
    }
}
