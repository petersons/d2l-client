<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Petersons\D2L\DTO\RichText;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#Question.QuestionInfo
 */
final class LongAnswer implements QuestionInfo
{
    public function __construct(
        private int $partId,
        private bool $studentEditorEnabled,
        private RichText $initialText,
        private RichText $answerKey,
        private ?bool $attachmentsEnabled,
    ) {
    }

    public function getPartId(): int
    {
        return $this->partId;
    }

    public function studentEditorEnabled(): bool
    {
        return $this->studentEditorEnabled;
    }

    public function getInitialText(): RichText
    {
        return $this->initialText;
    }

    public function getAnswerKey(): RichText
    {
        return $this->answerKey;
    }

    public function attachmentsEnabled(): ?bool
    {
        return $this->attachmentsEnabled;
    }

    public function toArray(): array
    {
        return [
            'PartId' => $this->partId,
            'EnableStudentEditor' => $this->studentEditorEnabled,
            'InitialText' => $this->initialText->toArray(),
            'AnswerKey' => $this->answerKey->toArray(),
            'EnableAttachments' => $this->attachmentsEnabled,
        ];
    }
}
