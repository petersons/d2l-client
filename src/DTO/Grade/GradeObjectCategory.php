<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Grade;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @link https://docs.valence.desire2learn.com/res/grade.html#Grade.GradeObjectCategory
 */
final class GradeObjectCategory implements Arrayable
{
    public function __construct(
        private int $id,
        private Collection $grades,
        private GradeObjectCategoryData $gradeObjectCategoryData,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<GradeObject>
     */
    public function getGrades(): Collection
    {
        return $this->grades;
    }

    public function getGradeObjectCategoryData(): GradeObjectCategoryData
    {
        return $this->gradeObjectCategoryData;
    }

    public function toArray(): array
    {
        return [
            'Id' => $this->id,
            'Grades' => $this->grades->toArray(),
            'Name' => $this->gradeObjectCategoryData->getName(),
            'ShortName' => $this->gradeObjectCategoryData->getShortName(),
            'CanExceedMax' => $this->gradeObjectCategoryData->canExceedMax(),
            'ExcludeFromFinalGrade' => $this->gradeObjectCategoryData->excludeFromFinalGrade(),
            'StartDate' => $this->gradeObjectCategoryData->getStartDate()?->toDateTime(),
            'EndDate' => $this->gradeObjectCategoryData->getEndDate()?->toDateTime(),
            'Weight' => $this->gradeObjectCategoryData->getWeight(),
            'MaxPoints' => $this->gradeObjectCategoryData->getMaxPoints(),
            'AutoPoints' => $this->gradeObjectCategoryData->autoPoints(),
            'WeightDistributionType' => $this->gradeObjectCategoryData->getWeightDistributionType(),
            'NumberOfHighestToDrop' => $this->gradeObjectCategoryData->getNumberOfHighestToDrop(),
            'NumberOfLowestToDrop' => $this->gradeObjectCategoryData->getNumberOfLowestToDrop(),
        ];
    }
}
