<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum;

use InvalidArgumentException;

final class RichTextInputType
{
    public const TEXT = 'Text';
    public const HTML = 'Html';

    public static function make(string $type): self
    {
        return match ($type) {
            self::TEXT => new self(self::TEXT),
            self::HTML => new self(self::HTML),
            default => throw new InvalidArgumentException(sprintf('Unknown rich text input type %d', $type)),
        };
    }

    public function type(): string
    {
        return $this->type;
    }

    private function __construct(private string $type) {}
}
