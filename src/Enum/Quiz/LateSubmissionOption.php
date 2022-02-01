<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum\Quiz;

use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/quiz.html#term-LATESUBMISSIONOPTION_T
 */
final class LateSubmissionOption
{
    public const ALLOW_NORMAL_SUBMISSION = 0;
    public const USE_LATE_LIMIT = 1;
    public const AUTO_SUBMIT_ATTEMPT = 2;

    public static function make(int $option): self
    {
        return match ($option) {
            self::ALLOW_NORMAL_SUBMISSION => new self(self::ALLOW_NORMAL_SUBMISSION),
            self::USE_LATE_LIMIT => new self(self::USE_LATE_LIMIT),
            self::AUTO_SUBMIT_ATTEMPT => new self(self::AUTO_SUBMIT_ATTEMPT),
            default => throw new InvalidArgumentException(sprintf('Unknown late submission option %d', $option)),
        };
    }

    public function getOption(): int
    {
        return $this->option;
    }

    private function __construct(private int $option)
    {
    }
}
