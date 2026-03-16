<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\Contracts\ClientInterface;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#Quiz.SpecialAccessData
 */
final readonly class QuizSpecialAccessRule implements Arrayable
{
    public function __construct(
        public CarbonImmutable|null $startDate,
        public CarbonImmutable|null $endDate,
        public CarbonImmutable|null $dueDate,
        public QuizSpecialAccessSubmissionTimeLimit|null $submissionTimeLimit,
        public QuizSpecialAccessAttemptsAllowed|null $attemptsAllowed,
    ) {
        if ($startDate !== null && $endDate !== null && $startDate > $endDate) {
            throw new \RuntimeException('Start date must be before end date');
        }

        if ($dueDate !== null && $endDate !== null && $dueDate > $endDate) {
            throw new \RuntimeException('Due date must be before end date');
        }
    }

    public function toArray(): array
    {
        return [
            'StartDate' => $this->startDate?->format(ClientInterface::D2L_DATETIME_FORMAT),
            'EndDate' => $this->endDate?->format(ClientInterface::D2L_DATETIME_FORMAT),
            'DueDate' => $this->dueDate?->format(ClientInterface::D2L_DATETIME_FORMAT),
            'SubmissionTimeLimit' => $this->submissionTimeLimit?->toArray(),
            'AttemptsAllowed' => $this->attemptsAllowed?->toArray(),
        ];
    }
}
