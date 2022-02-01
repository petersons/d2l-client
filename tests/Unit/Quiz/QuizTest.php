<?php

declare(strict_types=1);

namespace Tests\Unit\Quiz;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\DTO\Quiz\AttemptsAllowed;
use Petersons\D2L\DTO\Quiz\Description;
use Petersons\D2L\DTO\Quiz\Footer;
use Petersons\D2L\DTO\Quiz\Header;
use Petersons\D2L\DTO\Quiz\Instructions;
use Petersons\D2L\DTO\Quiz\LateSubmissionInfo;
use Petersons\D2L\DTO\Quiz\Quiz;
use Petersons\D2L\DTO\Quiz\SubmissionTimeLimit;
use Petersons\D2L\DTO\RichText;
use Petersons\D2L\Enum\Quiz\LateSubmissionOption;
use Petersons\D2L\Enum\Quiz\OverallGradeCalculationOption;
use PHPUnit\Framework\TestCase;

final class QuizTest extends TestCase
{
    public function testArrayRepresentation(): void
    {
        $quiz = new Quiz(
            46673,
            'Module 4 Prefixes Quiz',
            true,
            5,
            true,
            50354,
            true,
            new Instructions(new RichText('', ''), false),
            new Description(new RichText('', ''), false),
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-10-14T14:00:00.000Z'),
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-11-04T22:00:00.000Z'),
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-10-15T22:00:00.000Z'),
            false,
            new AttemptsAllowed(true, null),
            new LateSubmissionInfo(LateSubmissionOption::make(0), null),
            new SubmissionTimeLimit(false, false, 120),
            5,
            null,
            new Header(new RichText('', ''), false),
            new Footer(new RichText('', ''), false),
            false,
            false,
            false,
            null,
            OverallGradeCalculationOption::make(1),
            new Collection(),
            null,
            false,
            false,
            'https://ids.brightspace.com/activities/quiz/34907245-882D-4965-B3D6-0708A1D560F9-77531',
            false,
            false,
        );

        $this->assertSame(
            [
                'QuizId' => 46673,
                'Name' => 'Module 4 Prefixes Quiz',
                'IsActive' => true,
                'SortOrder' => 5,
                'AutoExportToGrades' => true,
                'GradeItemId' => 50354,
                'IsAutoSetGraded' => true,
                'Instructions' => [
                    'Text' => [
                        'Text' => '',
                        'Html' => '',
                    ],
                    'IsDisplayed' => false,
                ],
                'Description' => [
                    'Text' => [
                        'Text' => '',
                        'Html' => '',
                    ],
                    'IsDisplayed' => false,
                ],
                'StartDate' => '2021-10-14T14:00:00.000Z',
                'EndDate' => '2021-11-04T22:00:00.000Z',
                'DueDate' => '2021-10-15T22:00:00.000Z',
                'DisplayInCalendar' => false,
                'AttemptsAllowed' => [
                    'IsUnlimited' => true,
                    'NumberOfAttemptsAllowed' => null,
                ],
                'LateSubmissionInfo' => [
                    'LateSubmissionOption' => 0,
                    'LateLimitMinutes' => null,
                ],
                'SubmissionTimeLimit' => [
                    'IsEnforced' => false,
                    'ShowClock' => false,
                    'TimeLimitValue' => 120,
                ],
                'SubmissionGracePeriod' => 5,
                'Password' => null,
                'Header' => [
                    'Text' => [
                        'Text' => '',
                        'Html' => '',
                    ],
                    'IsDisplayed' => false,
                ],
                'Footer' => [
                    'Text' => [
                        'Text' => '',
                        'Html' => '',
                    ],
                    'IsDisplayed' => false,
                ],
                'AllowHints' => false,
                'DisableRightClick' => false,
                'DisablePagerAndAlerts' => false,
                'NotificationEmail' => null,
                'CalcTypeId' => 1,
                'RestrictIPAddressRange' => [],
                'CategoryId' => null,
                'PreventMovingBackwards' => false,
                'Shuffle' => false,
                'ActivityId' => 'https://ids.brightspace.com/activities/quiz/34907245-882D-4965-B3D6-0708A1D560F9-77531',
                'AllowOnlyUsersWithSpecialAccess' => false,
                'IsRetakeIncorrectOnly' => false,
            ],
            $quiz->toArray(),
        );
    }
}
