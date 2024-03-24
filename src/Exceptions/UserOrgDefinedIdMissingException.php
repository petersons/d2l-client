<?php

declare(strict_types=1);

namespace Petersons\D2L\Exceptions;

use Exception;
use Petersons\D2L\DTO\User\UserData;
use Throwable;

final class UserOrgDefinedIdMissingException extends Exception implements ExceptionInterface
{
    public function __construct(private UserData $userData, string $message = '', int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getUserData(): UserData
    {
        return $this->userData;
    }
}
