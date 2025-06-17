<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\DTO\RichText;

final class Footer implements Arrayable
{
    public function __construct(
        private RichText $text,
        private bool $isDisplayed,
    ) {}

    public function getText(): RichText
    {
        return $this->text;
    }

    public function isDisplayed(): bool
    {
        return $this->isDisplayed;
    }

    public function toArray(): array
    {
        return [
            'Text' => $this->text->toArray(),
            'IsDisplayed' => $this->isDisplayed,
        ];
    }
}
