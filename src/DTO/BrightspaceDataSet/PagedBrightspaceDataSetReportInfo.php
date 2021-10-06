<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\BrightspaceDataSet;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @link https://docs.valence.desire2learn.com/res/dataExport.html#BrightspaceDataSets.PagedBrightspaceDataSetReportInfo
 */
final class PagedBrightspaceDataSetReportInfo implements Arrayable
{
    public function __construct(
        private Collection $brightspaceDataSets,
        private ?string $nextPageUrl = null,
        private ?string $prevPageUrl = null
    ) {
    }

    public function getNextPageUrl(): ?string
    {
        return $this->nextPageUrl;
    }

    public function getPrevPageUrl(): ?string
    {
        return $this->prevPageUrl;
    }

    /**
     * @return Collection|BrightspaceDataSetReportInfo[]
     */
    public function getBrightspaceDataSets(): Collection
    {
        return $this->brightspaceDataSets;
    }

    public function toArray(): array
    {
        return [
            'NextPageUrl' => $this->nextPageUrl,
            'PrevPageUrl' => $this->prevPageUrl,
            'BrightspaceDataSets' => $this->brightspaceDataSets->toArray()
        ];
    }
}
