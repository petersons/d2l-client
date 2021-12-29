<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\ContentObject;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Petersons\D2L\DTO\RichText;

/**
 * @link https://docs.valence.desire2learn.com/res/content.html#Content.ContentObject
 */
final class Module
{
    public function __construct(
        private int $id,
        private Collection $structure,
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

    /**
     * @return Collection|Structure[]
     */
    public function getStructure(): Collection
    {
        return $this->structure;
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
