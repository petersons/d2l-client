<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\Enum\Quiz\OverallGradeCalculationOption;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#QuizReadData
 */
final class Quiz implements Arrayable
{
    public function __construct(
        private int $id,
        private string $name,
        private bool $isActive,
        private int $sortOrder,
        private bool|null $autoExportToGrades,
        private int|null $gradeItemId,
        private bool $isAutoSetGraded,
        private Instructions $instructions,
        private Description $description,
        private CarbonImmutable|null $startDate,
        private CarbonImmutable|null $endDate,
        private CarbonImmutable|null $dueDate,
        private bool $displayInCalendar,
        private AttemptsAllowed $attemptsAllowed,
        private LateSubmissionInfo $lateSubmissionInfo,
        private SubmissionTimeLimit $submissionTimeLimit,
        private int|null $submissionGracePeriod,
        private string|null $password,
        private Header $header,
        private Footer $footer,
        private bool $allowHints,
        private bool $disableRightClick,
        private bool $disablePagerAndAlerts,
        private string|null $notificationEmail,
        private OverallGradeCalculationOption $calcTypeId,
        private Collection $restrictIPAddressRange,
        private int|null $categoryId,
        private bool $preventMovingBackwards,
        private bool $shuffle,
        private string|null $activityId,
        private bool $allowOnlyUsersWithSpecialAccess,
        private bool $isRetakeIncorrectOnly,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getAutoExportToGrades(): bool|null
    {
        return $this->autoExportToGrades;
    }

    public function getGradeItemId(): int|null
    {
        return $this->gradeItemId;
    }

    public function isAutoSetGraded(): bool
    {
        return $this->isAutoSetGraded;
    }

    public function getInstructions(): Instructions
    {
        return $this->instructions;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function getStartDate(): CarbonImmutable|null
    {
        return $this->startDate;
    }

    public function getEndDate(): CarbonImmutable|null
    {
        return $this->endDate;
    }

    public function getDueDate(): CarbonImmutable|null
    {
        return $this->dueDate;
    }

    public function displayInCalendar(): bool
    {
        return $this->displayInCalendar;
    }

    public function getAttemptsAllowed(): AttemptsAllowed
    {
        return $this->attemptsAllowed;
    }

    public function getLateSubmissionInfo(): LateSubmissionInfo
    {
        return $this->lateSubmissionInfo;
    }

    public function getSubmissionTimeLimit(): SubmissionTimeLimit
    {
        return $this->submissionTimeLimit;
    }

    public function getSubmissionGracePeriod(): int|null
    {
        return $this->submissionGracePeriod;
    }

    public function getPassword(): string|null
    {
        return $this->password;
    }

    public function getHeader(): Header
    {
        return $this->header;
    }

    public function getFooter(): Footer
    {
        return $this->footer;
    }

    public function allowHints(): bool
    {
        return $this->allowHints;
    }

    public function disableRightClick(): bool
    {
        return $this->disableRightClick;
    }

    public function disablePagerAndAlerts(): bool
    {
        return $this->disablePagerAndAlerts;
    }

    public function getNotificationEmail(): string|null
    {
        return $this->notificationEmail;
    }

    public function getCalcTypeId(): OverallGradeCalculationOption
    {
        return $this->calcTypeId;
    }

    /**
     * @return Collection<RestrictIPAddressRange>
     */
    public function getRestrictIPAddressRange(): Collection
    {
        return $this->restrictIPAddressRange;
    }

    public function getCategoryId(): int|null
    {
        return $this->categoryId;
    }

    public function preventMovingBackwards(): bool
    {
        return $this->preventMovingBackwards;
    }

    public function shuffle(): bool
    {
        return $this->shuffle;
    }

    public function getActivityId(): string|null
    {
        return $this->activityId;
    }

    public function allowOnlyUsersWithSpecialAccess(): bool
    {
        return $this->allowOnlyUsersWithSpecialAccess;
    }

    public function isRetakeIncorrectOnly(): bool
    {
        return $this->isRetakeIncorrectOnly;
    }

    public function toArray(): array
    {
        return [
            'QuizId' => $this->id,
            'Name' => $this->name,
            'IsActive' => $this->isActive,
            'SortOrder' => $this->sortOrder,
            'AutoExportToGrades' => $this->autoExportToGrades,
            'GradeItemId' => $this->gradeItemId,
            'IsAutoSetGraded' => $this->isAutoSetGraded,
            'Instructions' => $this->instructions->toArray(),
            'Description' => $this->description->toArray(),
            'StartDate' => $this->startDate?->format(ClientInterface::D2L_DATETIME_FORMAT),
            'EndDate' => $this->endDate?->format(ClientInterface::D2L_DATETIME_FORMAT),
            'DueDate' => $this->dueDate?->format(ClientInterface::D2L_DATETIME_FORMAT),
            'DisplayInCalendar' => $this->displayInCalendar,
            'AttemptsAllowed' => $this->attemptsAllowed->toArray(),
            'LateSubmissionInfo' => $this->lateSubmissionInfo->toArray(),
            'SubmissionTimeLimit' => $this->submissionTimeLimit->toArray(),
            'SubmissionGracePeriod' => $this->submissionGracePeriod,
            'Password' => $this->password,
            'Header' => $this->header->toArray(),
            'Footer' => $this->footer->toArray(),
            'AllowHints' => $this->allowHints,
            'DisableRightClick' => $this->disableRightClick,
            'DisablePagerAndAlerts' => $this->disablePagerAndAlerts,
            'NotificationEmail' => $this->notificationEmail,
            'CalcTypeId' => $this->calcTypeId->getOption(),
            'RestrictIPAddressRange' => $this->restrictIPAddressRange->toArray(),
            'CategoryId' => $this->categoryId,
            'PreventMovingBackwards' => $this->preventMovingBackwards,
            'Shuffle' => $this->shuffle,
            'ActivityId' => $this->activityId,
            'AllowOnlyUsersWithSpecialAccess' => $this->allowOnlyUsersWithSpecialAccess,
            'IsRetakeIncorrectOnly' => $this->isRetakeIncorrectOnly,
        ];
    }
}
