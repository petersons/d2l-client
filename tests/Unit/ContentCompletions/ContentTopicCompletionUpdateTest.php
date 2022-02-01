<?php

declare(strict_types=1);

namespace Tests\Unit\ContentCompletions;

use Carbon\CarbonImmutable;
use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\DTO\ContentCompletions\ContentTopicCompletionUpdate;
use PHPUnit\Framework\TestCase;

final class ContentTopicCompletionUpdateTest extends TestCase
{
    public function testArrayRepresentation(): void
    {
        $time = CarbonImmutable::now();

        $updateContentTopicCompletion = new ContentTopicCompletionUpdate($time);

        $this->assertSame(
            [
                'CompletedDate' => $time->format(ClientInterface::D2L_DATETIME_FORMAT),
            ],
            $updateContentTopicCompletion->toArray(),
        );
    }

    public function testArrayRepresentationWhenTheTimeIsNull(): void
    {
        $updateContentTopicCompletion = new ContentTopicCompletionUpdate(null);

        $this->assertSame(
            [
                'CompletedDate' => null,
            ],
            $updateContentTopicCompletion->toArray(),
        );
    }
}
