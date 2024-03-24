<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum\DataExport;

use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/dataExport.html#DataExport.DataSetFilter
 */
final class ExportFilterType
{
    public const EXPORT_FILTER_TYPE_DATETIME = 1;
    public const EXPORT_FILTER_TYPE_ORGUNIT = 2;
    public const EXPORT_FILTER_TYPE_ROLE = 3;
    public const EXPORT_FILTER_TYPE_USER = 4;
    public const EXPORT_FILTER_TYPE_BOOLEAN = 5;

    public static function make(int $type): self
    {
        return match ($type) {
            self::EXPORT_FILTER_TYPE_DATETIME => new self(self::EXPORT_FILTER_TYPE_DATETIME),
            self::EXPORT_FILTER_TYPE_ORGUNIT => new self(self::EXPORT_FILTER_TYPE_ORGUNIT),
            self::EXPORT_FILTER_TYPE_ROLE => new self(self::EXPORT_FILTER_TYPE_ROLE),
            self::EXPORT_FILTER_TYPE_USER => new self(self::EXPORT_FILTER_TYPE_USER),
            self::EXPORT_FILTER_TYPE_BOOLEAN => new self(self::EXPORT_FILTER_TYPE_BOOLEAN),
            default => throw new InvalidArgumentException(sprintf('Unknown export filter type %d', $type)),
        };
    }

    public function __toString(): string
    {
        return (string) $this->type;
    }

    private function __construct(private int $type) {}
}
