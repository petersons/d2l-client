<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO\DataExport;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JsonSerializable;
use Petersons\D2L\Contracts\ClientInterface;

/**
 * @link https://docs.valence.desire2learn.com/res/dataExport.html#DataExport.ExportJobFilter
 */
final class ExportJobFilter implements Arrayable, JsonSerializable
{
    public const START_DATE_TYPE = 'startDate';
    public const END_DATE_TYPE = 'endDate';
    public const PARENT_ORG_UNIT_ID_TYPE = 'parentOrgUnitId';
    public const ROLES_TYPE = 'roles';
    public const USER_ID_TYPE = 'userId';

    private const SUPPORTED_TYPES = [
        self::START_DATE_TYPE,
        self::END_DATE_TYPE,
        self::PARENT_ORG_UNIT_ID_TYPE,
        self::ROLES_TYPE,
        self::USER_ID_TYPE,
    ];

    public static function startDate(CarbonImmutable $date): self
    {
        return new ExportJobFilter(self::START_DATE_TYPE, $date->format(ClientInterface::D2L_DATETIME_FORMAT));
    }

    public static function endDate(CarbonImmutable $date): self
    {
        return new ExportJobFilter(self::END_DATE_TYPE, $date->format(ClientInterface::D2L_DATETIME_FORMAT));
    }

    public static function parentOrgUnitId(int $parentOrgUnitId): self
    {
        return new ExportJobFilter(self::PARENT_ORG_UNIT_ID_TYPE, (string) $parentOrgUnitId);
    }

    public static function roles(Collection $roles): self
    {
        if ($roles->isEmpty()) {
            throw new InvalidArgumentException('Roles cannot be empty');
        }

        return new self(self::ROLES_TYPE, $roles->join(','));
    }

    public static function userId(int|null $userId): self
    {
        return new self(self::USER_ID_TYPE, (string) $userId);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function toArray(): array
    {
        return [
            'Name' => $this->name,
            'Value' => $this->value
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private function __construct(private string $name, private string $value)
    {
        if (!in_array($name, self::SUPPORTED_TYPES, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported filter name "%s" given.', $name));
        }
    }
}
