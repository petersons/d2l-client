<?php

declare(strict_types=1);

namespace Tests\Unit;

use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\DTO\Guid;
use Petersons\D2L\DTO\User\UserData;
use Petersons\D2L\Exceptions\InvalidGuidException;
use Petersons\D2L\Exceptions\UserOrgDefinedIdMissingException;
use Petersons\D2L\GuidGenerator;
use PHPUnit\Framework\TestCase;

final class GuidGeneratorTest extends TestCase
{
    public function testValidGuidGeneration(): void
    {
        $user = $this->getUser($orgDefinedId = bin2hex(random_bytes(10)));

        $expectedGuid = new Guid(bin2hex(random_bytes(10)));

        $d2lClient = $this->createMock(ClientInterface::class);
        $d2lClient->expects($this->once())
            ->method('generateExpiringGuid')
            ->with($orgDefinedId)
            ->willReturn($expectedGuid);
        $d2lClient->expects($this->once())
            ->method('validateGuid')
            ->with($expectedGuid, $orgDefinedId)
            ->willReturn(true);

        $guidGenerator = new GuidGenerator($d2lClient);
        $returnedGuid = $guidGenerator->generateForUser($user);

        $this->assertSame($expectedGuid, $returnedGuid);
    }

    public function testItThrowsInvalidGuidExceptionWhenTheGuidValidationFails(): void
    {
        $user = $this->getUser($orgDefinedId = bin2hex(random_bytes(10)));

        $generatedGuid = new Guid(bin2hex(random_bytes(10)));

        $d2lClient = $this->createMock(ClientInterface::class);
        $d2lClient->expects($this->once())
            ->method('generateExpiringGuid')
            ->with($orgDefinedId)
            ->willReturn($generatedGuid);
        $d2lClient->expects($this->once())
            ->method('validateGuid')
            ->with($generatedGuid, $orgDefinedId)
            ->willReturn(false);

        $guidGenerator = new GuidGenerator($d2lClient);

        $this->expectExceptionObject(new InvalidGuidException($generatedGuid));

        $guidGenerator->generateForUser($user);
    }

    public function testItThrowsUserOrgDefinedIdMissingExceptionWhenTheGivenUserDoesNotHaveOrgDefinedId(): void
    {
        $user = $this->getUser(null);

        $d2lClient = $this->createMock(ClientInterface::class);
        $d2lClient->expects($this->never())->method('generateExpiringGuid');
        $d2lClient->expects($this->never())->method('validateGuid');

        $guidGenerator = new GuidGenerator($d2lClient);

        $this->expectExceptionObject(new UserOrgDefinedIdMissingException($user));

        $guidGenerator->generateForUser($user);
    }

    private function getUser(string|null $orgDefinedId): UserData
    {
        return new UserData(
            555,
            55,
            '',
            null,
            '',
            '',
            '',
            $orgDefinedId,
            '',
            true,
            null,
        );
    }
}
