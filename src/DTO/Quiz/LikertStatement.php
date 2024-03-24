<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\DTO\RichText;

final class LikertStatement implements Arrayable
{
    public function __construct(
        private int $partId,
        private RichText $statement,
    ) {}

    public function getPartId(): int
    {
        return $this->partId;
    }

    public function getStatement(): RichText
    {
        return $this->statement;
    }

    public function toArray(): array
    {
        return [
            'PartId' => $this->partId,
            'Statement' => $this->statement->toArray(),
        ];
    }
}
