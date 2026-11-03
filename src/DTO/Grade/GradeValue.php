<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Grade;

use Carbon\CarbonImmutable;
use Petersons\D2L\DTO\RichText;
use Petersons\D2L\Enum\Grade\GradeObjectType;

/**
 * The points properties are null for Text grade objects, which are not computable, and for the
 * default value D2L can return before a grade has been assessed.
 *
 * @link https://docs.valence.desire2learn.com/res/grade.html#Grade.GradeValue
 */
final readonly class GradeValue
{
    public function __construct(
        public string $displayedGrade,
        public int $gradeObjectIdentifier,
        public string $gradeObjectName,
        public GradeObjectType $gradeObjectType,
        public string|null $gradeObjectTypeName,
        public RichText $comments,
        public RichText $privateComments,
        public CarbonImmutable|null $lastModified,
        public int|null $lastModifiedBy,
        public CarbonImmutable|null $releasedDate,
        public float|null $pointsNumerator,
        public float|null $pointsDenominator,
        public float|null $weightedNumerator,
        public float|null $weightedDenominator,
    ) {}
}
