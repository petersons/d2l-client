<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\DataExport;

use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\Enum\DataExport\ExportFilterType;

/**
 * @link https://docs.valence.desire2learn.com/res/dataExport.html#DataExport.DataSetFilter
 */
final class DataSetFilter implements Arrayable
{
    public function __construct(
        private string $name,
        private ExportFilterType $type,
        private ?string $description,
        private ?string $defaultValue
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ExportFilterType
    {
        return $this->type;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function toArray(): array
    {
        return [
            'Name' => $this->name,
            'Type' => (string) $this->type,
            'Description' => $this->description,
            'DefaultValue' => $this->defaultValue,
        ];
    }
}
