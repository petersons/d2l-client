<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\ContentObject;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Petersons\D2L\DTO\RichText;
use Petersons\D2L\Enum\ContentObject\Type;

/**
 * @link https://docs.valence.desire2learn.com/res/content.html#Content.ContentObject
 */
final class Module extends ContentObject
{
    public function __construct(
        int $id,
        private Collection $structure,
        ?CarbonImmutable $startDate,
        ?CarbonImmutable $endDate,
        ?CarbonImmutable $dueDate,
        bool $isHidden,
        bool $isLocked,
        string $title,
        string $shortTitle,
        ?RichText $description,
        ?int $parentModuleId,
        ?CarbonImmutable $lastModifiedDate
    ) {
        parent::__construct(
            $id,
            Type::module(),
            $startDate,
            $endDate,
            $dueDate,
            $isHidden,
            $isLocked,
            $title,
            $shortTitle,
            $description,
            $parentModuleId,
            $lastModifiedDate
        );
    }

    /**
     * @return Collection|Structure[]
     */
    public function getStructure(): Collection
    {
        return $this->structure;
    }
}
