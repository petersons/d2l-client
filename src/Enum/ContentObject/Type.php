<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum\ContentObject;

use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/content.html#term-CONTENT_T
 */
final class Type
{
    public const MODULE_TYPE = 0;
    public const TOPIC_TYPE = 1;

    public static function make(int $type): self
    {
        return match ($type) {
            self::MODULE_TYPE => new self(self::MODULE_TYPE),
            self::TOPIC_TYPE => new self(self::TOPIC_TYPE),
            default => throw new InvalidArgumentException(sprintf('Unknown content object type %d', $type)),
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
