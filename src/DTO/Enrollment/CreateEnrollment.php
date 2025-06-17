<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Enrollment;

final class CreateEnrollment
{
    public function __construct(
        private int $orgUnitId,
        private int $userId,
        private int $roleId,
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

    public function toArray(): array
    {
        return [
            'OrgUnitId' => $this->getOrgUnitId(),
            'UserId' => $this->getUserId(),
            'RoleId' => $this->getRoleId(),
        ];
    }
}
