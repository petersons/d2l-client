<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Enrollment;

use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\DTO\User\User;

/**
 * @link https://docs.valence.desire2learn.com/res/enroll.html#Enrollment.OrgUnitUser
 */
final class OrganizationUnitUser implements Arrayable
{
    public function __construct(
        private User $user,
        private RoleInfo $roleInfo
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRoleInfo(): RoleInfo
    {
        return $this->roleInfo;
    }

    public function toArray(): array
    {
        return [
            'User' => $this->user->toArray(),
            'Role' => $this->roleInfo->toArray()
        ];
    }
}
