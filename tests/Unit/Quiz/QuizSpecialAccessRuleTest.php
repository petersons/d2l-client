<?php

declare(strict_types=1);

namespace Tests\Unit\Quiz;

use Carbon\CarbonImmutable;
use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\DTO\Quiz\QuizSpecialAccessAttemptsAllowed;
use Petersons\D2L\DTO\Quiz\QuizSpecialAccessRule;
use Petersons\D2L\DTO\Quiz\QuizSpecialAccessSubmissionTimeLimit;
use PHPUnit\Framework\TestCase;

final class QuizSpecialAccessRuleTest extends TestCase
{
    public function testArrayRepresentation(): void
    {
        $quizSpecialAccess = new QuizSpecialAccessRule(
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-12-23T10:46:22.183Z'),
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-12-28T12:46:22.183Z'),
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-12-25T15:46:22.183Z'),
            new QuizSpecialAccessSubmissionTimeLimit(true, 9999),
            new QuizSpecialAccessAttemptsAllowed(false, 10),
        );

        $this->assertSame(
            [
                'StartDate' => '2021-12-23T10:46:22.183Z',
                'EndDate' => '2021-12-28T12:46:22.183Z',
                'DueDate' => '2021-12-25T15:46:22.183Z',
                'SubmissionTimeLimit' => [
                    'IsEnforced' => true,
                    'TimeLimitValue' => 9999,
                ],
                'AttemptsAllowed' => [
                    'IsUnlimited' => false,
                    'NumberOfAttemptsAllowed' => 10,
                ],
            ],
            $quizSpecialAccess->toArray(),
        );
    }

    public function testStartDateMustBeBeforeEndDate(): void
    {
        $this->expectExceptionObject(new \RuntimeException('Start date must be before end date'));

        new QuizSpecialAccessRule(
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-12-28T10:46:23.183Z'),
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-12-28T10:46:22.183Z'),
            null,
            new QuizSpecialAccessSubmissionTimeLimit(true, 9999),
            new QuizSpecialAccessAttemptsAllowed(false, 10),
        );
    }

    public function testDueDateMustBeBeforeEndDate(): void
    {
        $this->expectExceptionObject(new \RuntimeException('Due date must be before end date'));

        new QuizSpecialAccessRule(
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-12-23T10:46:22.183Z'),
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-12-28T12:46:22.183Z'),
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-12-28T12:46:23.183Z'),
            new QuizSpecialAccessSubmissionTimeLimit(true, 9999),
            new QuizSpecialAccessAttemptsAllowed(false, 10),
        );
    }
}
