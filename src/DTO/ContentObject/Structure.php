<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\ContentObject;

use Carbon\CarbonImmutable;
use Petersons\D2L\Enum\ContentObject\Type;

final class Structure
{
    public function __construct(
        private int $id,
        private string $title,
        private string $shortTitle,
        private Type $type,
        private ?CarbonImmutable $lastModifiedDate
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getShortTitle(): string
    {
        return $this->shortTitle;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getLastModifiedDate(): ?CarbonImmutable
    {
        return $this->lastModifiedDate;
    }
}
