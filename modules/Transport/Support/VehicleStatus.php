<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum VehicleStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case UNDER_MAINTENANCE = 'under_maintenance';
    case LENT = 'lent';

    public static function all(): array
    {
        return [
            self::ACTIVE->value,
            self::INACTIVE->value,
            self::UNDER_MAINTENANCE->value,
            self::LENT->value,
        ];
    }

    public static function toArray(): array
    {
        return array_column(VehicleStatus::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::ACTIVE => 'Ativo',
            self::INACTIVE => 'Inativo',
            self::UNDER_MAINTENANCE => 'Em Manutenção',
            self::LENT => 'Emprestado',
        };
    }
}
