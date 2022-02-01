<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\ContentObject;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\DTO\RichText;
use Petersons\D2L\Enum\ContentObject\Type;

abstract class ContentObject implements Arrayable
{
    public function __construct(
        private int $id,
        private Type $type,
        private ?CarbonImmutable $startDate,
        private ?CarbonImmutable $endDate,
        private ?CarbonImmutable $dueDate,
        private bool $isHidden,
        private bool $isLocked,
        private string $title,
        private string $shortTitle,
        private ?RichText $description,
        private ?int $parentModuleId,
        private ?CarbonImmutable $lastModifiedDate
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getStartDate(): ?CarbonImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): ?CarbonImmutable
    {
        return $this->endDate;
    }

    public function getDueDate(): ?CarbonImmutable
    {
        return $this->dueDate;
    }

    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getShortTitle(): string
    {
        return $this->shortTitle;
    }

    public function getDescription(): ?RichText
    {
        return $this->description;
    }

    public function getParentModuleId(): ?int
    {
        return $this->parentModuleId;
    }

    public function getLastModifiedDate(): ?CarbonImmutable
    {
        return $this->lastModifiedDate;
    }
}
