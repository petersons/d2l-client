<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Grade;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @link https://docs.valence.desire2learn.com/res/grade.html#Grade.GradeObject
 */
final class GradeObject implements Arrayable
{
    public function __construct(private string $name) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            'Name' => $this->name,
        ];
    }
}
