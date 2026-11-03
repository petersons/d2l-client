<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Grade;

use Petersons\D2L\DTO\User\User;

/**
 * @link https://docs.valence.desire2learn.com/res/grade.html#Grade.UserGradeValue
 */
final readonly class UserGradeValue
{
    public function __construct(
        public User $user,
        /**
         * Null when the user has no grade value, even if Brightspace is configured to treat
         * ungraded items as 0.
         */
        public GradeValue|null $gradeValue,
    ) {}
}
