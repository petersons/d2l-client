<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Grade;

use Carbon\CarbonImmutable;

/**
 * @link https://docs.valence.desire2learn.com/res/grade.html#Grade.GradeObjectCategoryData
 */
final class GradeObjectCategoryData
{
    public function __construct(
        private string $name,
        private string $shortName,
        private bool $canExceedMax,
        private bool $excludeFromFinalGrade,
        private ?CarbonImmutable $startDate,
        private ?CarbonImmutable $endDate,
        private ?float $weight,
        private ?float $maxPoints,
        private ?bool $autoPoints,
        private ?int $weightDistributionType,
        private ?int $numberOfHighestToDrop,
        private ?int $numberOfLowestToDrop
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function canExceedMax(): bool
    {
        return $this->canExceedMax;
    }

    public function excludeFromFinalGrade(): bool
    {
        return $this->excludeFromFinalGrade;
    }

    public function getStartDate(): ?CarbonImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): ?CarbonImmutable
    {
        return $this->endDate;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function getMaxPoints(): ?float
    {
        return $this->maxPoints;
    }

    public function autoPoints(): ?bool
    {
        return $this->autoPoints;
    }

    public function getWeightDistributionType(): ?int
    {
        return $this->weightDistributionType;
    }

    public function getNumberOfHighestToDrop(): ?int
    {
        return $this->numberOfHighestToDrop;
    }

    public function getNumberOfLowestToDrop(): ?int
    {
        return $this->numberOfLowestToDrop;
    }
}
