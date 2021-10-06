<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\DTO\RichText;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#Question.QuestionData
 */
final class QuizQuestion implements Arrayable
{
    public function __construct(
        private int $id,
        private QuizQuestionType $type,
        private ?string $name,
        private RichText $text,
        private float $points,
        private int $difficulty,
        private bool $isBonus,
        private bool $isMandatory,
        private ?RichText $hint,
        private ?RichText $feedback,
        private CarbonImmutable $lastModifiedAt,
        private ?int $lastModifiedBy,
        private int $sectionId,
        private int $templateId,
        private int $templateVersionId
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): QuizQuestionType
    {
        return $this->type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getText(): RichText
    {
        return $this->text;
    }

    public function getPoints(): float
    {
        return $this->points;
    }

    public function getDifficulty(): int
    {
        return $this->difficulty;
    }

    public function isBonus(): bool
    {
        return $this->isBonus;
    }

    public function isMandatory(): bool
    {
        return $this->isMandatory;
    }

    public function getHint(): ?RichText
    {
        return $this->hint;
    }

    public function getFeedback(): ?RichText
    {
        return $this->feedback;
    }

    public function getLastModifiedAt(): CarbonImmutable
    {
        return $this->lastModifiedAt;
    }

    public function getLastModifiedBy(): ?int
    {
        return $this->lastModifiedBy;
    }

    public function getSectionId(): int
    {
        return $this->sectionId;
    }

    public function getTemplateId(): int
    {
        return $this->templateId;
    }

    public function getTemplateVersionId(): int
    {
        return $this->templateVersionId;
    }

    public function toArray(): array
    {
        return [
            'QuestionId' => $this->id,
            'QuestionTypeId' => $this->type->type(),
            'Name' => $this->name,
            'QuestionText' => $this->text->toArray(),
            'Points' => $this->points,
            'Difficulty' => $this->difficulty,
            'Bonus' => $this->isBonus,
            'Mandatory' => $this->isMandatory,
            'Hint' => $this->hint ? $this->hint->toArray() : null,
            'Feedback' => $this->feedback->toArray(),
            'LastModified' => $this->lastModifiedAt->toDateTime(),
            'LastModifiedBy' => $this->lastModifiedBy,
            'SectionId' => $this->sectionId,
            'QuestionTemplateId' => $this->templateId,
            'QuestionTemplateVersionId' => $this->templateVersionId,
        ];
    }
}
