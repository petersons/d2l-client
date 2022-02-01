<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\Enum\RichTextInputType;

/**
 * @link https://docs.valence.desire2learn.com/basic/conventions.html#term-RichTextInput
 */
final class RichTextInput implements Arrayable
{
    public function __construct(
        private string $content,
        private RichTextInputType $richTextInputType,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getRichTextInputType(): RichTextInputType
    {
        return $this->richTextInputType;
    }

    public function toArray(): array
    {
        return [
            'Content' => $this->getContent(),
            'Type' => $this->getRichTextInputType()->type(),
        ];
    }
}
