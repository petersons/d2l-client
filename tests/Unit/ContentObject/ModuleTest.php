<?php

declare(strict_types=1);

namespace Tests\Unit\ContentObject;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\DTO\ContentObject\Module;
use Petersons\D2L\DTO\ContentObject\Structure;
use Petersons\D2L\DTO\RichText;
use Petersons\D2L\Enum\ContentObject\Type;
use PHPUnit\Framework\TestCase;

final class ModuleTest extends TestCase
{
    public function testArrayRepresentation(): void
    {
        $module = new Module(
            321606,
            Collection::make([
                new Structure(321666, 'Suffix Elements 1-5', '', Type::make(1), CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-12-23T15:46:22.120Z')),
                new Structure(321667, 'Suffix Elements 6-10', '', Type::make(1), CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2021-12-23T15:46:22.183Z'))
            ]),
            null,
            null,
            null,
            false,
            false,
            'Suffix Elements 1 - 20',
            '',
            new RichText('', ''),
            321584,
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, '2020-08-03T19:12:47.607Z'),
        );

        $this->assertSame(
            [
                'Structure' => [
                    [
                        'Id' => 321666,
                        'Title' => 'Suffix Elements 1-5',
                        'ShortTitle' => '',
                        'Type' => 1,
                        'LastModifiedDate' => '2021-12-23T15:46:22.120Z',
                    ],
                    [
                        'Id' => 321667,
                        'Title' => 'Suffix Elements 6-10',
                        'ShortTitle' => '',
                        'Type' => 1,
                        'LastModifiedDate' => '2021-12-23T15:46:22.183Z',
                    ],
                ],
                'ModuleStartDate' => null,
                'ModuleEndDate' => null,
                'ModuleDueDate' => null,
                'IsHidden' => false,
                'IsLocked' => false,
                'Id' => 321606,
                'Title' => 'Suffix Elements 1 - 20',
                'ShortTitle' => '',
                'Type' => 0,
                'Description' => [
                    'Text' => '',
                    'Html' => '',
                ],
                'ParentModuleId' => 321584,
                'LastModifiedDate' => '2020-08-03T19:12:47.607Z',
            ],
            $module->toArray(),
        );
    }
}
