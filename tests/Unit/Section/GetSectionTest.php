<?php

declare(strict_types=1);

namespace Tests\Unit\Section;

use Illuminate\Support\Collection;
use Petersons\D2L\DTO\RichText;
use Petersons\D2L\DTO\Section\Section;
use PHPUnit\Framework\TestCase;

final class GetSectionTest extends TestCase
{
    public function testArrayRepresentation(): void
    {
        $sectionId = 1;
        $name = 'Marvel';
        $code = '1234';
        $description = new RichText('pero', '');
        $enrollments = new Collection();

        $createSectionEnrollment = new Section($sectionId, $name, $code, $description, $enrollments);

        $this->assertSame(
            [
                'SectionId' => $sectionId,
                'Name' => $name,
                'Code' => $code,
                'Description' => $description->toArray(),
                'Enrollments' => $enrollments->toArray(),
            ],
            $createSectionEnrollment->toArray()
        );
    }
}
