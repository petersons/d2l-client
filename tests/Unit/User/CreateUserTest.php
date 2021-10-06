<?php

declare(strict_types=1);

namespace Tests\Unit\User;

use Petersons\D2L\DTO\User\CreateUser;
use PHPUnit\Framework\TestCase;

final class CreateUserTest extends TestCase
{
    public function testArrayRepresentation(): void
    {
        $orgDefinedId = bin2hex(random_bytes(10));
        $firstName = bin2hex(random_bytes(10));
        $middleName = bin2hex(random_bytes(10));
        $lastName = bin2hex(random_bytes(10));
        $externalEmail = bin2hex(random_bytes(10));
        $username = bin2hex(random_bytes(10));
        $roleId = random_int(1, 10000);
        $isActive = true;
        $sendCreationEmail = false;

        $createUser = new CreateUser(
            $orgDefinedId,
            $firstName,
            $middleName,
            $lastName,
            $externalEmail,
            $username,
            $roleId,
            $isActive,
            $sendCreationEmail
        );

        $this->assertSame(
            [
                'OrgDefinedId' => $orgDefinedId,
                'FirstName' => $firstName,
                'MiddleName' => $middleName,
                'LastName' => $lastName,
                'ExternalEmail' => $externalEmail,
                'UserName' => $username,
                'RoleId' => $roleId,
                'IsActive' => $isActive,
                'SendCreationEmail' => $sendCreationEmail,
            ],
            $createUser->toArray()
        );
    }
}
