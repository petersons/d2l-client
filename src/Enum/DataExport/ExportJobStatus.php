<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum\DataExport;

use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/dataExport.html#term-EXPORTJOBSTATUS_T
 */
final class ExportJobStatus
{
    public const STATUS_QUEUED = 0;
    public const STATUS_PROCESSING = 1;
    public const STATUS_COMPLETE = 2;
    public const STATUS_ERROR = 3;
    public const STATUS_DELETED = 4;

    private array $descriptions = [
        self::STATUS_QUEUED => 'Export job has been received for processing.',
        self::STATUS_PROCESSING => 'Currently in process of exporting data set.',
        self::STATUS_COMPLETE => 'Export completed successfully.',
        self::STATUS_ERROR => 'An error occurred when processing the export.',
        self::STATUS_DELETED => 'File associated with the completed export was deleted from the file system.'
    ];

    public function __construct(private int $status)
    {
        if (!array_key_exists($status, $this->descriptions)) {
            throw new InvalidArgumentException(sprintf('Unknown status %d', $status));
        }
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getDescription(): string
    {
        return $this->descriptions[$this->status];
    }
}
