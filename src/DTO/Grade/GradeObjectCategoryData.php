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
        private CarbonImmutable|null $startDate,
        private CarbonImmutable|null $endDate,
        private float|null $weight,
        private float|null $maxPoints,
        private bool|null $autoPoints,
        private int|null $weightDistributionType,
        private int|null $numberOfHighestToDrop,
        private int|null $numberOfLowestToDrop
    ) {}

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

    public function getStartDate(): CarbonImmutable|null
    {
        return $this->startDate;
    }

    public function getEndDate(): CarbonImmutable|null
    {
        return $this->endDate;
    }

    public function getWeight(): float|null
    {
        return $this->weight;
    }

    public function getMaxPoints(): float|null
    {
        return $this->maxPoints;
    }

    public function autoPoints(): bool|null
    {
        return $this->autoPoints;
    }

    public function getWeightDistributionType(): int|null
    {
        return $this->weightDistributionType;
    }

    public function getNumberOfHighestToDrop(): int|null
    {
        return $this->numberOfHighestToDrop;
    }

    public function getNumberOfLowestToDrop(): int|null
    {
        return $this->numberOfLowestToDrop;
    }
}
