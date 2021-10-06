<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\BrightspaceDataSet;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @link https://docs.valence.desire2learn.com/res/dataExport.html#BrightspaceDataSets.DataSetReportInfo
 */
final class DataSetReportInfo implements Arrayable
{
    public function __construct(
        private string $pluginId,
        private string $name,
        private string $description,
        private ?CarbonImmutable $createdAt,
        private ?string $downloadLink,
        private ?float $downloadSize
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

    public function getCreatedAt(): ?CarbonImmutable
    {
        return $this->createdAt;
    }

    public function getDownloadLink(): ?string
    {
        return $this->downloadLink;
    }

    public function getDownloadSize(): ?float
    {
        return $this->downloadSize;
    }

    public function toArray(): array
    {
        return [
            'PluginId' => $this->pluginId,
            'Name' => $this->name,
            'Description' => $this->description,
            'CreatedDate' => $this->createdAt ? $this->createdAt->toDateTime() : null,
            'DownloadLink' => $this->downloadLink,
            'DownloadSize' => $this->downloadSize,
        ];
    }
}
