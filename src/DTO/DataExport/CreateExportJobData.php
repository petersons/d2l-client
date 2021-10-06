<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\DataExport;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Illuminate\Support\ItemNotFoundException;
use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/dataExport.html#DataExport.CreateExportJobData
 */
final class CreateExportJobData implements Arrayable, Jsonable
{
    private string $dataSetId;

    /**
     * @var ExportJobFilter[]
     */
    private array $filters;

    public function __construct(string $dataSetId, array $filters)
    {
        $this->dataSetId = $dataSetId;
        $this->filters = $filters;

        $filterNames = Collection::make($filters)->map(function (ExportJobFilter $exportJobFilter) {
            return $exportJobFilter->getName();
        });

        if ($filterNames->count() !== $filterNames->unique()->count()) {
            throw new InvalidArgumentException('There cannot be two filters with the same name in the array.');
        }

        try {
            Collection::make($filters)->sole(function (ExportJobFilter $exportJobFilter) {
                return ExportJobFilter::START_DATE_TYPE === $exportJobFilter->getName();
            });

            Collection::make($filters)->sole(function (ExportJobFilter $exportJobFilter) {
                return ExportJobFilter::END_DATE_TYPE === $exportJobFilter->getName();
            });
        } catch (ItemNotFoundException $e) {
            throw new InvalidArgumentException('Start date and end date filters are required.');
        }
    }

    public function getDataSetId(): string
    {
        return $this->dataSetId;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function toArray(): array
    {
        return [
            'DataSetId' => $this->dataSetId,
            'Filters' => $this->filters
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
