<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\CarbonImmutable;
use Petersons\D2L\Contracts\ClientInterface;
use PHPUnit\Framework\TestCase;

/**
 * @link https://docs.valence.desire2learn.com/basic/conventions.html#term-UTCDateTime
 */
final class DateTimeFormatterTest extends TestCase
{
    public function testItFormatsTheDateAccordingToD2LDateTimeFormatRules(): void
    {
        $date = CarbonImmutable::createFromTimestampMsUTC(2410434930067);

        $this->assertSame('2046-05-20T13:15:30.067Z', $date->format(ClientInterface::D2L_DATETIME_FORMAT));
    }
}
