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
        private ?CarbonImmutable $createdDate = null,
        private ?string $downloadLink = null,
        private ?float $downloadSize = null,
        private ?string $version = null,
        private ?Collection $previousDataSets = null,
        private ?CarbonImmutable $queuedForProcessingDate = null
    ) {
    }

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

    public function getCreatedDate(): ?CarbonImmutable
    {
        return $this->createdDate;
    }

    public function getDownloadLink(): ?string
    {
        return $this->downloadLink;
    }

    public function getDownloadSize(): ?float
    {
        return $this->downloadSize;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @return Collection|null|BrightspaceDataSetReportInfo[]
     */
    public function getPreviousDataSets(): ?Collection
    {
        return $this->previousDataSets;
    }

    public function getQueuedForProcessingDate(): ?CarbonImmutable
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
            'CreatedDate' => null !== $this->createdDate ? $this->createdDate->toDateTime() : null,
            'DownloadLink' => $this->downloadLink,
            'DownloadSize' => $this->downloadSize,
            'Version' => $this->version,
            'PreviousDataSets' => null !== $this->previousDataSets ? $this->previousDataSets->toArray() : null,
            'QueuedForProcessingDate' => null !== $this->queuedForProcessingDate ? $this->queuedForProcessingDate->toDateTime() : null,
        ];
    }
}
