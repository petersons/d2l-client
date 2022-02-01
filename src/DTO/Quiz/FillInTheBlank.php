<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Support\Collection;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#Question.QuestionInfo
 */
final class FillInTheBlank implements QuestionInfo
{
    public function __construct(
        private Collection $texts,
        private Collection $blanks
    ) {
    }

    /**
     * @return Collection|FillInTheBlankText[]
     */
    public function getTexts(): Collection
    {
        return $this->texts;
    }

    /**
     * @return Collection|FillInTheBlankBlank[]
     */
    public function getBlanks(): Collection
    {
        return $this->blanks;
    }

    public function toArray(): array
    {
        return [
            'Texts' => $this->texts->toArray(),
            'Blanks' => $this->blanks->toArray(),
        ];
    }
}
