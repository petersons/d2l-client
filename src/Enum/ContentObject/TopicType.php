<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum\ContentObject;

use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/content.html#term-TOPIC_T
 */
final class TopicType
{
    public const FILE = 1;
    public const LINK = 3;
    public const SCORM_2004 = 5;
    public const SCORM_2004_ROOT = 6;
    public const SCORM_1_2 = 7;
    public const SCORM_1_2_ROOT = 8;

    public static function make(int $type): self
    {
        return match ($type) {
            self::FILE => new self(self::FILE),
            self::LINK => new self(self::LINK),
            self::SCORM_2004 => new self(self::SCORM_2004),
            self::SCORM_2004_ROOT => new self(self::SCORM_2004_ROOT),
            self::SCORM_1_2 => new self(self::SCORM_1_2),
            self::SCORM_1_2_ROOT => new self(self::SCORM_1_2_ROOT),
            default => throw new InvalidArgumentException(sprintf('Unknown topic type %d', $type)),
        };
    }

    public function getType(): int
    {
        return $this->type;
    }

    private function __construct(private int $type)
    {
    }
}
