<?php

declare(strict_types=1);

namespace Tests\Unit\Enrollment;

use Petersons\D2L\DTO\Enrollment\CreateSectionEnrollment;
use PHPUnit\Framework\TestCase;

final class CreateSectionEnrollmentTest extends TestCase
{
    public function testArrayRepresentation(): void
    {
        $orgUnitId = random_int(1, 10000);
        $userId = random_int(1, 10000);
        $sectionId = random_int(1, 10000);
        $createSectionEnrollment = new CreateSectionEnrollment($orgUnitId, $userId, $sectionId);

        $this->assertSame(
            [
                'UserId' => $userId,
            ],
            $createSectionEnrollment->toArray()
        );
    }
}
