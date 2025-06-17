<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @link https://docs.valence.desire2learn.com/basic/conventions.html?highlight=richtext#term-RichText
 */
final class RichText implements Arrayable
{
    public function __construct(
        private string $text,
        private string|null $html,
    ) {}

    public function getText(): string
    {
        return $this->text;
    }

    public function getHtml(): string|null
    {
        return $this->html;
    }

    public function toArray(): array
    {
        return [
            'Text' => $this->text,
            'Html' => $this->html,
        ];
    }
}
