<?php

declare(strict_types=1);

namespace Tests\Unit\Enrollment;

use Petersons\D2L\DTO\Enrollment\CreateEnrollment;
use PHPUnit\Framework\TestCase;

final class CreateEnrollmentTest extends TestCase
{
    public function testArrayRepresentation(): void
    {
        $orgUnitId = random_int(1, 10000);
        $userId = random_int(1, 10000);
        $roleId = random_int(1, 10000);
        $createEnrollment = new CreateEnrollment($orgUnitId, $userId, $roleId);

        $this->assertSame(
            [
                'OrgUnitId' => $orgUnitId,
                'UserId' => $userId,
                'RoleId' => $roleId,
            ],
            $createEnrollment->toArray(),
        );
    }
}
