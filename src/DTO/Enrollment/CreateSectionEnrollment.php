<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Enrollment;

/**
 * @link https://docs.valence.desire2learn.com/res/enroll.html#Section.SectionEnrollment
 */
final class CreateSectionEnrollment
{
    public function __construct(
        private int $orgUnitId,
        private int $userId,
        private int $sectionId,
    ) {}

    public function getOrgUnitId(): int
    {
        return $this->orgUnitId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getSectionId(): int
    {
        return $this->sectionId;
    }

    public function toArray(): array
    {
        return [
            'UserId' => $this->getUserId(),
        ];
    }
}
