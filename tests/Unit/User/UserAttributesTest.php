<?php

declare(strict_types=1);

namespace Tests\Unit\User;

use Petersons\D2L\DTO\User\UserAttribute;
use Petersons\D2L\DTO\User\UserAttributes;
use PHPUnit\Framework\TestCase;

final class UserAttributesTest extends TestCase
{
    public function testArrayRepresentation(): void
    {
        $lmsUserId = random_int(1, 10000);
        $attributes = [];
        $attributes[] = new UserAttribute('_companyname', ['Foo']);
        $attributes[] = new UserAttribute('_hireddate', ['2020-05-01']);

        $userAttributes = new UserAttributes($lmsUserId, $attributes);

        $this->assertSame(
            [
                'UserId' => $lmsUserId,
                'Attributes' => [
                    [
                        'AttributeId' => '_companyname',
                        'Value' => ['Foo'],
                    ],
                    [
                        'AttributeId' => '_hireddate',
                        'Value' => ['2020-05-01'],
                    ],
                ],
            ],
            $userAttributes->toArray(),
        );
    }
}
