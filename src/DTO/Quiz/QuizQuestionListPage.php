<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Support\Collection;
use Petersons\D2L\DTO\ObjectListPage;

/**
 * @method Collection|QuizQuestion[] getObjects
 */
final class QuizQuestionListPage extends ObjectListPage
{
    protected function checkIsInstanceOfExpectedClass(object $object): bool
    {
        return $object instanceof QuizQuestion;
    }
}
