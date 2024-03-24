<?php

declare(strict_types=1);

namespace Petersons\D2L\Exceptions;

use Exception;
use Petersons\D2L\DTO\Guid;
use Throwable;

final class InvalidGuidException extends Exception implements ExceptionInterface
{
    public function __construct(private Guid $guid, string $message = '', int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getGuid(): Guid
    {
        return $this->guid;
    }
}
