<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\DataExport;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\Enum\DataExport\ExportJobStatus;

/**
 * @link https://docs.valence.desire2learn.com/res/dataExport.html#DataExport.ExportJobData
 */
final class ExportJobData implements Arrayable
{
    public function __construct(
        private string $exportJobId,
        private string $dataSetId,
        private string $name,
        private CarbonImmutable $submitDate,
        private ExportJobStatus $status,
        private string $category
    ) {
    }

    public function getExportJobId(): string
    {
        return $this->exportJobId;
    }

    public function getDataSetId(): string
    {
        return $this->dataSetId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSubmitDate(): CarbonImmutable
    {
        return $this->submitDate;
    }

    public function getStatus(): ExportJobStatus
    {
        return $this->status;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function toArray(): array
    {
        return [
            'ExportJobId' => $this->exportJobId,
            'DataSetId' => $this->dataSetId,
            'Name' => $this->name,
            'SubmitDate' => $this->submitDate,
            'Status' => $this->status->getStatus(),
            'Category' => $this->category
        ];
    }
}
