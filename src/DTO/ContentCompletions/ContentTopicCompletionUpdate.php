<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\ContentCompletions;

use Carbon\CarbonImmutable;
use Petersons\D2L\Contracts\ClientInterface;

/**
 * @link https://docs.valence.desire2learn.com/res/content.html#ContentCompletions.ContentTopicCompletionUpdate
 */
final class ContentTopicCompletionUpdate
{
    public function __construct(private ?CarbonImmutable $completedDate)
    {
    }

    public function getCompletedDate(): ?CarbonImmutable
    {
        return $this->completedDate;
    }

    public function toArray(): array
    {
        return [
            'CompletedDate' => $this->getCompletedDate()?->format(ClientInterface::D2L_DATETIME_FORMAT),
        ];
    }
}
