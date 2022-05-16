<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Section;

use Illuminate\Support\Collection;
use Petersons\D2L\DTO\RichText;

final class Section
{
    public function __construct(
        public int        $sectionId,
        public string     $name,
        public string     $code,
        public RichText $description,
        public Collection $enrollments,
    ) {
    }

    public function getSectionId(): int
    {
        return $this->sectionId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDescription(): RichText
    {
        return $this->description;
    }

    public function getEnrollments(): Collection
    {
        return $this->enrollments;
    }

    public function toArray(): array
    {
        return [
            'SectionId' => $this->sectionId,
            'Name' => $this->name,
            'Code' => $this->code,
            'Description' => $this->description->toArray(),
            'Enrollments' => $this->enrollments->toArray()
        ];
    }


}