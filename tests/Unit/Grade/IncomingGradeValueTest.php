<?php

declare(strict_types=1);

namespace Tests\Unit\Grade;

use InvalidArgumentException;
use Petersons\D2L\DTO\Grade\IncomingGradeValue;
use PHPUnit\Framework\TestCase;

final class IncomingGradeValueTest extends TestCase
{
    public function testCreatingNumericTypeFromArray(): void
    {
        $data = [
            'Comments' => [
                'Text' => '',
                'Html' => '',
            ],
            'PrivateComments' => [
                'Text' => '',
                'Html' => '',
            ],
            'GradeObjectType' => 1,
            'PointsNumerator' => 5.0,
        ];

        $incomingGradeValue = IncomingGradeValue::createFromArray($data);

        $this->assertSame($data, $incomingGradeValue->toArray());
    }

    public function testCreatingPassFailTypeFromArray(): void
    {
        $data = [
            'Comments' => [
                'Text' => '',
                'Html' => '',
            ],
            'PrivateComments' => [
                'Text' => '',
                'Html' => '',
            ],
            'GradeObjectType' => 2,
            'Pass' => true,
        ];

        $incomingGradeValue = IncomingGradeValue::createFromArray($data);

        $this->assertSame($data, $incomingGradeValue->toArray());
    }

    public function testCreatingSelectBoxTypeFromArray(): void
    {
        $data = [
            'Comments' => [
                'Text' => '',
                'Html' => '',
            ],
            'PrivateComments' => [
                'Text' => '',
                'Html' => '',
            ],
            'GradeObjectType' => 3,
            'Value' => 'foo',
        ];

        $incomingGradeValue = IncomingGradeValue::createFromArray($data);

        $this->assertSame($data, $incomingGradeValue->toArray());
    }

    public function testCreatingTextTypeFromArray(): void
    {
        $data = [
            'Comments' => [
                'Text' => '',
                'Html' => '',
            ],
            'PrivateComments' => [
                'Text' => '',
                'Html' => '',
            ],
            'GradeObjectType' => 4,
            'Text' => 'foo',
        ];

        $incomingGradeValue = IncomingGradeValue::createFromArray($data);

        $this->assertSame($data, $incomingGradeValue->toArray());
    }

    public function testCreatingFromArrayThrowsExceptionIfGradeObjectTypeIsNotPresentInTheGivenPayload(): void
    {
        $data = [
            'Comments' => [
                'Text' => '',
                'Html' => '',
            ],
            'PrivateComments' => [
                'Text' => '',
                'Html' => '',
            ],
            'Text' => 'foo',
        ];

        $this->expectExceptionObject(new InvalidArgumentException('Grade object type ID is a required parameter'));

        IncomingGradeValue::createFromArray($data);
    }

    public function testCreatingFromArrayThrowsExceptionIfGradeObjectTypeThatIsGivenInThePayloadIsNotSupported(): void
    {
        $data = [
            'Comments' => [
                'Text' => '',
                'Html' => '',
            ],
            'PrivateComments' => [
                'Text' => '',
                'Html' => '',
            ],
            'GradeObjectType' => 5,
            'Text' => 'foo',
        ];

        $this->expectExceptionObject(new InvalidArgumentException('The given grade object type ID 5 is not supported'));

        IncomingGradeValue::createFromArray($data);
    }
}
