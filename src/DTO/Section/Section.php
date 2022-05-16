<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Section;

use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\DTO\RichText;

/**
 * @link https://docs.valence.desire2learn.com/res/enroll.html#Section.SectionData
 */
final class Section implements Arrayable
{
    public function __construct(
        private int        $sectionId,
        private string     $name,
        private string     $code,
        private RichText $description,
        private array $enrollments,
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

    /**
     * @return array&int[]
     */
    public function getEnrollments(): array
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
            'Enrollments' => $this->enrollments
        ];
    }
}
