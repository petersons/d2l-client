<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\Grade;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use Petersons\D2L\DTO\RichTextInput;
use Petersons\D2L\Enum\Grade\GradeObjectType;
use Petersons\D2L\Enum\RichTextInputType;

/**
 * @link https://docs.valence.desire2learn.com/res/grade.html#Grade.IncomingGradeValue
 */
final class IncomingGradeValue implements Arrayable
{
    public static function numeric(RichTextInput $comments, RichTextInput $privateComments, float $pointsNumerator): self
    {
        return new self(
            $comments,
            $privateComments,
            GradeObjectType::make(GradeObjectType::NUMBERS),
            $pointsNumerator,
        );
    }

    public static function passFail(RichTextInput $comments, RichTextInput $privateComments, bool $pass): self
    {
        return new self(
            $comments,
            $privateComments,
            GradeObjectType::make(GradeObjectType::PASS_FAIL),
            null,
            $pass,
        );
    }

    public static function selectBox(RichTextInput $comments, RichTextInput $privateComments, string $value): self
    {
        return new self(
            $comments,
            $privateComments,
            GradeObjectType::make(GradeObjectType::SELECT_BOX),
            null,
            null,
            $value,
        );
    }

    public static function text(RichTextInput $comments, RichTextInput $privateComments, string $text): self
    {
        return new self(
            $comments,
            $privateComments,
            GradeObjectType::make(GradeObjectType::TEXT),
            null,
            null,
            null,
            $text,
        );
    }

    public static function createFromArray(array $data): self
    {
        if (!isset($data['GradeObjectType'])) {
            throw new InvalidArgumentException('Grade object type ID is a required parameter');
        }

        $gradeObjectType = GradeObjectType::make($data['GradeObjectType']);

        $isAllowedGradeObjectType = $gradeObjectType->isNumbers()
            || $gradeObjectType->isPassFail()
            ||
            $gradeObjectType->isSelectBox()
            ||
            $gradeObjectType->isText();

        if (!$isAllowedGradeObjectType) {
            throw new InvalidArgumentException(sprintf('The given grade object type ID %d is not supported', $data['GradeObjectType']));
        }

        if ($gradeObjectType->isNumbers()) {
            return self::numeric(
                new RichTextInput($data['Comments']['Content'], RichTextInputType::make($data['Comments']['Type'])),
                new RichTextInput($data['PrivateComments']['Content'], RichTextInputType::make($data['PrivateComments']['Type'])),
                $data['PointsNumerator'],
            );
        }

        if ($gradeObjectType->isPassFail()) {
            return self::passFail(
                new RichTextInput($data['Comments']['Content'], RichTextInputType::make($data['Comments']['Type'])),
                new RichTextInput($data['PrivateComments']['Content'], RichTextInputType::make($data['PrivateComments']['Type'])),
                $data['Pass'],
            );
        }

        if ($gradeObjectType->isSelectBox()) {
            return self::selectBox(
                new RichTextInput($data['Comments']['Content'], RichTextInputType::make($data['Comments']['Type'])),
                new RichTextInput($data['PrivateComments']['Content'], RichTextInputType::make($data['PrivateComments']['Type'])),
                $data['Value'],
            );
        }

        return self::text(
            new RichTextInput($data['Comments']['Content'], RichTextInputType::make($data['Comments']['Type'])),
            new RichTextInput($data['PrivateComments']['Content'], RichTextInputType::make($data['PrivateComments']['Type'])),
            $data['Text'],
        );
    }

    public function toArray(): array
    {
        $arrayRepresentation = [];

        $arrayRepresentation['Comments'] = $this->comments->toArray();
        $arrayRepresentation['PrivateComments'] = $this->privateComments->toArray();
        $arrayRepresentation['GradeObjectType'] = $this->gradeObjectType->type();

        if ($this->gradeObjectType->isNumbers()) {
            $arrayRepresentation['PointsNumerator'] = $this->pointsNumerator;

            return $arrayRepresentation;
        }

        if ($this->gradeObjectType->isPassFail()) {
            $arrayRepresentation['Pass'] = $this->pass;

            return $arrayRepresentation;
        }

        if ($this->gradeObjectType->isSelectBox()) {
            $arrayRepresentation['Value'] = $this->value;

            return $arrayRepresentation;
        }

        $arrayRepresentation['Text'] = $this->text;

        return $arrayRepresentation;
    }

    private function __construct(
        private RichTextInput $comments,
        private RichTextInput $privateComments,
        private GradeObjectType $gradeObjectType,
        private float|null $pointsNumerator = null,
        private bool|null $pass = null,
        private string|null $value = null,
        private string|null $text = null,
    ) {}
}
