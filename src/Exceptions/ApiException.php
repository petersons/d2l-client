<?php

declare(strict_types=1);

namespace Petersons\D2L\Exceptions;

use Exception;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as SymfonyExceptionInterface;

final class ApiException extends Exception implements ExceptionInterface
{
    public static function fromSymfonyHttpException(SymfonyExceptionInterface $exception): self
    {
        return new self($exception->getMessage(), $exception->getCode(), $exception);
    }
}
