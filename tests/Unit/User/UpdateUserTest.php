<?php

declare(strict_types=1);

namespace Tests\Unit\User;

use Petersons\D2L\DTO\User\UpdateUser;
use PHPUnit\Framework\TestCase;

final class UpdateUserTest extends TestCase
{
    public function testArrayRepresentation(): void
    {
        $lmsUserId = random_int(1, 10000);
        $orgDefinedId = bin2hex(random_bytes(10));
        $firstName = bin2hex(random_bytes(10));
        $middleName = bin2hex(random_bytes(10));
        $lastName = bin2hex(random_bytes(10));
        $externalEmail = bin2hex(random_bytes(10));
        $username = bin2hex(random_bytes(10));
        $isActive = true;

        $updateUser = new UpdateUser(
            $lmsUserId,
            $orgDefinedId,
            $firstName,
            $middleName,
            $lastName,
            $externalEmail,
            $username,
            $isActive,
        );

        $this->assertSame(
            [
                'OrgDefinedId' => $orgDefinedId,
                'FirstName' => $firstName,
                'MiddleName' => $middleName,
                'LastName' => $lastName,
                'ExternalEmail' => $externalEmail,
                'UserName' => $username,
                'Activation' => [
                    'IsActive' => $isActive,
                ],
            ],
            $updateUser->toArray(),
        );
    }
}
