<?php

declare(strict_types=1);

namespace Tests\Unit\DataExport;

use Carbon\CarbonImmutable;
use InvalidArgumentException;
use Petersons\D2L\DTO\DataExport\CreateExportJobData;
use Petersons\D2L\DTO\DataExport\ExportJobFilter;
use PHPUnit\Framework\TestCase;

final class CreateExportJobDataTest extends TestCase
{
    public function testCanBeConstructed(): void
    {
        $createExportJobData = new CreateExportJobData(
            'foo',
            [ExportJobFilter::startDate(CarbonImmutable::now()), ExportJobFilter::endDate(CarbonImmutable::now())],
        );
        $this->assertInstanceOf(CreateExportJobData::class, $createExportJobData);
    }

    public function testCreateExportJobDataCannotBeCreatedWithoutStartDateFilter(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Start date and end date filters are required.'));

        new CreateExportJobData(
            'foo',
            [ExportJobFilter::endDate(CarbonImmutable::now())],
        );
    }

    public function testCreateExportJobDataCannotBeCreatedWithoutEndDateFilter(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Start date and end date filters are required.'));

        new CreateExportJobData(
            'foo',
            [ExportJobFilter::startDate(CarbonImmutable::now())],
        );
    }

    public function testCreateExportJobDataCannotBeCreatedWithDuplicatedFilters(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('There cannot be two filters with the same name in the array.'));

        new CreateExportJobData(
            'foo',
            [
                ExportJobFilter::startDate(CarbonImmutable::now()),
                ExportJobFilter::endDate(CarbonImmutable::now()),
                ExportJobFilter::parentOrgUnitId(5),
                ExportJobFilter::parentOrgUnitId(6),
            ],
        );
    }
}
