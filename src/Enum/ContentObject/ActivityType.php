<?php

declare(strict_types=1);

namespace Petersons\D2L\Enum\ContentObject;

use InvalidArgumentException;

/**
 * @link https://docs.valence.desire2learn.com/res/content.html#term-ACTIVITYTYPE_T
 */
final class ActivityType
{
    public const UNKNOWN_ACTIVITY = -1;
    public const MODULE = 0;
    public const FILE = 1;
    public const LINK = 2;
    public const DROPBOX = 3;
    public const QUIZ = 4;
    public const DISCUSSION_FORUM = 5;
    public const DISCUSSION_TOPIC = 6;
    public const LTI = 7;
    public const CHAT = 8;
    public const SCHEDULE = 9;
    public const CHECKLIST = 10;
    public const SELF_ASSESSMENT = 11;
    public const SURVEY = 12;
    public const ONLINE_ROOM = 13;
    public const SCORM_1_3 = 20;
    public const SCORM_1_3_ROOT = 21;
    public const SCORM_1_2 = 22;
    public const SCORM_1_2_ROOT = 23;

    public static function make(int $type): self
    {
        return match ($type) {
            self::UNKNOWN_ACTIVITY => new self(self::UNKNOWN_ACTIVITY),
            self::MODULE => new self(self::MODULE),
            self::FILE => new self(self::FILE),
            self::LINK => new self(self::LINK),
            self::DROPBOX => new self(self::DROPBOX),
            self::QUIZ => new self(self::QUIZ),
            self::DISCUSSION_FORUM => new self(self::DISCUSSION_FORUM),
            self::DISCUSSION_TOPIC => new self(self::DISCUSSION_TOPIC),
            self::LTI => new self(self::LTI),
            self::CHAT => new self(self::CHAT),
            self::SCHEDULE => new self(self::SCHEDULE),
            self::CHECKLIST => new self(self::CHECKLIST),
            self::SELF_ASSESSMENT => new self(self::SELF_ASSESSMENT),
            self::SURVEY => new self(self::SURVEY),
            self::ONLINE_ROOM => new self(self::ONLINE_ROOM),
            self::SCORM_1_3 => new self(self::SCORM_1_3),
            self::SCORM_1_3_ROOT => new self(self::SCORM_1_3_ROOT),
            self::SCORM_1_2 => new self(self::SCORM_1_2),
            self::SCORM_1_2_ROOT => new self(self::SCORM_1_2_ROOT),
            default => throw new InvalidArgumentException(sprintf('Unknown activity type %d', $type)),
        };
    }

    public function getType(): int
    {
        return $this->type;
    }

    private function __construct(private int $type) {}
}
