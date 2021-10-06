<?php

declare(strict_types=1);

namespace Tests\Unit\DataExport;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Petersons\D2L\DTO\DataExport\ExportJobFilter;
use PHPUnit\Framework\TestCase;

final class ExportJobFilterTest extends TestCase
{
    public function testCreatingStartDateFilter(): void
    {
        $date = CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2021-05-21 16:47:08');

        $filter = ExportJobFilter::startDate($date);

        $this->assertSame('startDate', $filter->getName());
        $this->assertSame('2021-05-21T16:47:08.000Z', $filter->getValue());
    }

    public function testCreatingEndDateFilter(): void
    {
        $date = CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2021-05-21 16:48:07');

        $filter = ExportJobFilter::endDate($date);

        $this->assertSame('endDate', $filter->getName());
        $this->assertSame('2021-05-21T16:48:07.000Z', $filter->getValue());
    }

    public function testCreatingParentOrgUnitIdFilter(): void
    {
        $filter = ExportJobFilter::parentOrgUnitId(5);

        $this->assertSame('parentOrgUnitId', $filter->getName());
        $this->assertSame('5', $filter->getValue());
    }

    public function testCreatingRolesFilterWhenThereIsOnlyOneEntryInTheArray(): void
    {
        $filter = ExportJobFilter::roles(Collection::make([578]));

        $this->assertSame('roles', $filter->getName());
        $this->assertSame('578', $filter->getValue());
    }

    public function testCreatingRolesFilterWhenThereAreMultipleEntriesInTheArray(): void
    {
        $filter = ExportJobFilter::roles(Collection::make([578, 901]));

        $this->assertSame('roles', $filter->getName());
        $this->assertSame('578,901', $filter->getValue());
    }

    public function testCreatingRolesFilterWhenThereNoEntriesInTheArrayThrowsAnException(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Roles cannot be empty'));
        ExportJobFilter::roles(Collection::make([]));
    }

    public function testArrayRepresentation(): void
    {
        $date = CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2021-05-21 16:47:08');

        $filter = ExportJobFilter::startDate($date);

        $this->assertSame(
            [
                'Name' => $filter->getName(),
                'Value' => $filter->getValue(),
            ],
            $filter->toArray()
        );
    }

    public function testJsonRepresentation(): void
    {
        $date = CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2021-05-21 16:47:08');

        $filter = ExportJobFilter::startDate($date);

        $this->assertSame(
            '{"Name":"startDate","Value":"2021-05-21T16:47:08.000Z"}',
            json_encode($filter)
        );
    }
}
