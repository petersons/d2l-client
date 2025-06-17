<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\ContentObject;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\Enum\ContentObject\Type;

final class Structure implements Arrayable
{
    public function __construct(
        private int $id,
        private string $title,
        private string $shortTitle,
        private Type $type,
        private CarbonImmutable|null $lastModifiedDate,
    ) {}

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

    public function getLastModifiedDate(): CarbonImmutable|null
    {
        return $this->lastModifiedDate;
    }

    public function toArray(): array
    {
        return [
            'Id' => $this->id,
            'Title' => $this->title,
            'ShortTitle' => $this->shortTitle,
            'Type' => $this->type->getType(),
            'LastModifiedDate' => $this->lastModifiedDate?->format(ClientInterface::D2L_DATETIME_FORMAT),
        ];
    }
}
