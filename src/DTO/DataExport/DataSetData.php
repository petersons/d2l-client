<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\DataExport;

use Illuminate\Support\Collection;

/**
 * @link https://docs.valence.desire2learn.com/res/dataExport.html#DataExport.DataSetData
 */
final class DataSetData
{
    public function __construct(
        private string $id,
        private string $name,
        private string $description,
        private string $category,
        private Collection $filters
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @return Collection<DataSetFilter>
     */
    public function getFilters(): Collection
    {
        return $this->filters;
    }
}
