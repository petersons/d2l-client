<?php

declare(strict_types=1);

namespace Petersons\D2L;

use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\DTO\Guid;
use Petersons\D2L\DTO\User\UserData;
use Petersons\D2L\Exceptions\ApiException;
use Petersons\D2L\Exceptions\InvalidGuidException;
use Petersons\D2L\Exceptions\UserOrgDefinedIdMissingException;

final class GuidGenerator
{
    public function __construct(private ClientInterface $d2lClient) {}

    /**
     * @throws UserOrgDefinedIdMissingException
     * @throws ApiException
     * @throws InvalidGuidException
     */
    public function generateForUser(UserData $user): Guid
    {
        if (null === $user->getOrgDefinedId()) {
            throw new UserOrgDefinedIdMissingException($user);
        }

        $guid = $this->d2lClient->generateExpiringGuid($user->getOrgDefinedId());

        $isGuidValid = $this->d2lClient->validateGuid($guid, $user->getOrgDefinedId());

        if ($isGuidValid) {
            return $guid;
        }

        throw new InvalidGuidException($guid);
    }
}
