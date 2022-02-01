<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\DTO\RichText;

final class FillInTheBlankText implements Arrayable
{
    public function __construct(private RichText $text)
    {
    }

    public function getText(): RichText
    {
        return $this->text;
    }

    public function toArray(): array
    {
        return [
            'Text' => $this->text->toArray(),
        ];
    }
}
