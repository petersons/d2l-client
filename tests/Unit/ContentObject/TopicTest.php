<?php

declare(strict_types=1);

namespace Tests\Unit\ContentObject;

use Carbon\CarbonImmutable;
use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\DTO\ContentObject\Topic;
use Petersons\D2L\DTO\RichText;
use Petersons\D2L\Enum\ContentObject\ActivityType;
use Petersons\D2L\Enum\ContentObject\TopicType;
use PHPUnit\Framework\TestCase;

final class TopicTest extends TestCase
{
    public function testArrayRepresentation(): void
    {
        $topic = new Topic(
            321655,
            TopicType::make(3),
            'https://learn.petersons.com/d2l/lor/viewer/view.d2l?ou=515376&loIdentId=200',
            null,
            null,
            null,
            false,
            false,
            false,
            'Introduction to the Dean Vaughn Total Retention System®',
            '',
            new RichText('', ''),
            321600,
            null,
            false,
            null,
            null,
            ActivityType::make(2),
            null,
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-12-23T15:46:21.353Z'),
        );

        $this->assertSame(
            [
                'TopicType' => 3,
                'Url' => 'https://learn.petersons.com/d2l/lor/viewer/view.d2l?ou=515376&loIdentId=200',
                'StartDate' => null,
                'EndDate' => null,
                'DueDate' => null,
                'IsHidden' => false,
                'IsLocked' => false,
                'OpenAsExternalResource' => false,
                'Id' => 321655,
                'Title' => 'Introduction to the Dean Vaughn Total Retention System®',
                'ShortTitle' => '',
                'Type' => 1,
                'Description' => [
                    'Text' => '',
                    'Html' => '',
                ],
                'ParentModuleId' => 321600,
                'ActivityId' => null,
                'IsExempt' => false,
                'ToolId' => null,
                'ToolItemId' => null,
                'ActivityType' => 2,
                'GradeItemId' => null,
                'LastModifiedDate' => '2021-12-23T15:46:21.353Z',
            ],
            $topic->toArray(),
        );
    }
}
