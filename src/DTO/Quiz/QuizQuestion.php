<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\DTO\RichText;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#Question.QuestionData
 */
final class QuizQuestion implements Arrayable
{
    public function __construct(
        private int $id,
        private QuizQuestionType $type,
        private string|null $name,
        private RichText $text,
        private float $points,
        private int $difficulty,
        private bool $isBonus,
        private bool $isMandatory,
        private RichText|null $hint,
        private RichText|null $feedback,
        private CarbonImmutable $lastModifiedAt,
        private int|null $lastModifiedBy,
        private int $sectionId,
        private int $templateId,
        private int $templateVersionId,
        private QuestionInfo|null $questionInfo,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): QuizQuestionType
    {
        return $this->type;
    }

    public function getName(): string|null
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

    public function getHint(): RichText|null
    {
        return $this->hint;
    }

    public function getFeedback(): RichText|null
    {
        return $this->feedback;
    }

    public function getLastModifiedAt(): CarbonImmutable
    {
        return $this->lastModifiedAt;
    }

    public function getLastModifiedBy(): int|null
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

    public function getQuestionInfo(): QuestionInfo|null
    {
        return $this->questionInfo;
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
            'Hint' => $this->hint?->toArray(),
            'Feedback' => $this->feedback?->toArray(),
            'LastModified' => $this->lastModifiedAt->format(ClientInterface::D2L_DATETIME_FORMAT),
            'LastModifiedBy' => $this->lastModifiedBy,
            'SectionId' => $this->sectionId,
            'QuestionTemplateId' => $this->templateId,
            'QuestionTemplateVersionId' => $this->templateVersionId,
            'QuestionInfo' => $this->questionInfo?->toArray(),
        ];
    }
}
