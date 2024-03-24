<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\ContentObject;

use Carbon\CarbonImmutable;
use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\DTO\RichText;
use Petersons\D2L\Enum\ContentObject\ActivityType;
use Petersons\D2L\Enum\ContentObject\TopicType;
use Petersons\D2L\Enum\ContentObject\Type;

/**
 * @link https://docs.valence.desire2learn.com/res/content.html#Content.ContentObject
 */
final class Topic extends ContentObject
{
    public function __construct(
        int $id,
        private TopicType $topicType,
        private string $url,
        CarbonImmutable|null $startDate,
        CarbonImmutable|null $endDate,
        CarbonImmutable|null $dueDate,
        bool $isHidden,
        bool $isLocked,
        private bool|null $openAsExternalResource,
        string $title,
        string $shortTitle,
        RichText|null $description,
        int $parentModuleId,
        private string|null $activityId,
        private bool $isExempt,
        private int|null $toolId,
        private int|null $toolItemId,
        private ActivityType $activityType,
        private int|null $gradeItemId,
        CarbonImmutable|null $lastModifiedDate,
    ) {
        parent::__construct(
            $id,
            Type::topic(),
            $startDate,
            $endDate,
            $dueDate,
            $isHidden,
            $isLocked,
            $title,
            $shortTitle,
            $description,
            $parentModuleId,
            $lastModifiedDate
        );
    }

    public function getTopicType(): TopicType
    {
        return $this->topicType;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getOpenAsExternalResource(): bool|null
    {
        return $this->openAsExternalResource;
    }

    public function getActivityId(): string|null
    {
        return $this->activityId;
    }

    public function isExempt(): bool
    {
        return $this->isExempt;
    }

    public function getToolId(): int|null
    {
        return $this->toolId;
    }

    public function getToolItemId(): int|null
    {
        return $this->toolItemId;
    }

    public function getActivityType(): ActivityType
    {
        return $this->activityType;
    }

    public function getGradeItemId(): int|null
    {
        return $this->gradeItemId;
    }

    public function toArray(): array
    {
        return [
            'TopicType' => $this->getTopicType()->getType(),
            'Url' => $this->getUrl(),
            'StartDate' => $this->getStartDate()?->format(ClientInterface::D2L_DATETIME_FORMAT),
            'EndDate' => $this->getEndDate()?->format(ClientInterface::D2L_DATETIME_FORMAT),
            'DueDate' => $this->getDueDate()?->format(ClientInterface::D2L_DATETIME_FORMAT),
            'IsHidden' => $this->isHidden(),
            'IsLocked' => $this->isLocked(),
            'OpenAsExternalResource' => $this->getOpenAsExternalResource(),
            'Id' => $this->getId(),
            'Title' => $this->getTitle(),
            'ShortTitle' => $this->getShortTitle(),
            'Type' => $this->getType()->getType(),
            'Description' => $this->getDescription()?->toArray(),
            'ParentModuleId' => $this->getParentModuleId(),
            'ActivityId' => $this->getActivityId(),
            'IsExempt' => $this->isExempt(),
            'ToolId' => $this->getToolId(),
            'ToolItemId' => $this->getToolItemId(),
            'ActivityType' => $this->getActivityType()->getType(),
            'GradeItemId' => $this->getGradeItemId(),
            'LastModifiedDate' => $this->getLastModifiedDate()?->format(ClientInterface::D2L_DATETIME_FORMAT),
        ];
    }
}
