<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Enrollment;

final class Enrollment
{
    public function __construct(
        private int $orgUnitId,
        private int $userId,
        private int $roleId,
        private bool $isCascading
    ) {}

    public function getOrgUnitId(): int
    {
        return $this->orgUnitId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function isCascading(): bool
    {
        return $this->isCascading;
    }
}
