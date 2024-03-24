<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\BrightspaceDataSet;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @link https://docs.valence.desire2learn.com/res/dataExport.html#BrightspaceDataSets.BrightspaceDataSetReportInfo
 */
final class BrightspaceDataSetReportInfo implements Arrayable
{
    public function __construct(
        private string $pluginId,
        private string $name,
        private string $description,
        private bool $fullDataset,
        private CarbonImmutable|null $createdDate = null,
        private string|null $downloadLink = null,
        private float|null $downloadSize = null,
        private string|null $version = null,
        private Collection|null $previousDataSets = null,
        private CarbonImmutable|null $queuedForProcessingDate = null
    ) {}

    public function getPluginId(): string
    {
        return $this->pluginId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isFullDataset(): bool
    {
        return $this->fullDataset;
    }

    public function getCreatedDate(): CarbonImmutable|null
    {
        return $this->createdDate;
    }

    public function getDownloadLink(): string|null
    {
        return $this->downloadLink;
    }

    public function getDownloadSize(): float|null
    {
        return $this->downloadSize;
    }

    public function getVersion(): string|null
    {
        return $this->version;
    }

    /**
     * @return Collection<BrightspaceDataSetReportInfo>|null
     */
    public function getPreviousDataSets(): Collection|null
    {
        return $this->previousDataSets;
    }

    public function getQueuedForProcessingDate(): CarbonImmutable|null
    {
        return $this->queuedForProcessingDate;
    }

    public function toArray(): array
    {
        return [
            'PluginId' => $this->pluginId,
            'Name' => $this->name,
            'Description' => $this->description,
            'FullDataSet' => $this->fullDataset,
            'CreatedDate' => $this->createdDate?->toDateTime(),
            'DownloadLink' => $this->downloadLink,
            'DownloadSize' => $this->downloadSize,
            'Version' => $this->version,
            'PreviousDataSets' => $this->previousDataSets?->toArray(),
            'QueuedForProcessingDate' => $this->queuedForProcessingDate?->toDateTime(),
        ];
    }
}
