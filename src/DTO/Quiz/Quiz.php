<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#QuizReadData
 */
final class Quiz implements Arrayable
{
    public function __construct(
        private int $id,
        private string $name,
        private bool $isActive,
        private ?int $gradeItemId
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getGradeItemId(): ?int
    {
        return $this->gradeItemId;
    }

    public function toArray(): array
    {
        return [
            'QuizId' => $this->id,
            'Name' => $this->name,
            'IsActive' => $this->isActive,
            'GradeItemId' => $this->gradeItemId,
        ];
    }
}
