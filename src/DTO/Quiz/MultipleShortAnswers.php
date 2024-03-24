<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Quiz;

use Illuminate\Support\Collection;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#Question.QuestionInfo
 */
final class MultipleShortAnswers implements QuestionInfo
{
    public function __construct(
        private Collection $partIds,
        private int $boxes,
        private int $rows,
        private int $columns,
        private Collection $answers
    ) {}

    /**
     * @return Collection<int>
     */
    public function getPartIds(): Collection
    {
        return $this->partIds;
    }

    public function getBoxes(): int
    {
        return $this->boxes;
    }

    public function getRows(): int
    {
        return $this->rows;
    }

    public function getColumns(): int
    {
        return $this->columns;
    }

    /**
     * @return Collection<MultipleShortAnswer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function toArray(): array
    {
        return [
            'PartIds' => $this->partIds->toArray(),
            'Boxes' => $this->boxes,
            'Rows' => $this->rows,
            'Columns' => $this->columns,
            'Answers' => $this->answers->toArray(),
        ];
    }
}
