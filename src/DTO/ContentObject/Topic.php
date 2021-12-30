<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\ContentObject;

use Carbon\CarbonImmutable;
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
        ?CarbonImmutable $startDate,
        ?CarbonImmutable $endDate,
        ?CarbonImmutable $dueDate,
        bool $isHidden,
        bool $isLocked,
        private ?bool $openAsExternalResource,
        string $title,
        string $shortTitle,
        ?RichText $description,
        int $parentModuleId,
        private ?string $activityId,
        private bool $isExempt,
        private ?int $toolId,
        private ?int $toolItemId,
        private ActivityType $activityType,
        private ?int $gradeItemId,
        ?CarbonImmutable $lastModifiedDate,
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

    public function getOpenAsExternalResource(): ?bool
    {
        return $this->openAsExternalResource;
    }

    public function getActivityId(): ?string
    {
        return $this->activityId;
    }

    public function isExempt(): bool
    {
        return $this->isExempt;
    }

    public function getToolId(): ?int
    {
        return $this->toolId;
    }

    public function getToolItemId(): ?int
    {
        return $this->toolItemId;
    }

    public function getActivityType(): ActivityType
    {
        return $this->activityType;
    }

    public function getGradeItemId(): ?int
    {
        return $this->gradeItemId;
    }
}
