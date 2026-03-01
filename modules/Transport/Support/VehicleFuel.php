<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum VehicleFuel: string
{
    case GASOLINE = 'gasoline';
    case ETHANOL = 'ethanol';
    case DIESEL = 'diesel';
    case ELECTRIC = 'electric';

    public static function all(): array
    {
        return [
            self::GASOLINE,
            self::ETHANOL,
            self::DIESEL,
            self::ELECTRIC,
        ];
    }

    public static function toArray(): array
    {
        return array_column(VehicleFuel::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::GASOLINE => 'Gasolina',
            self::ETHANOL => 'Etanol',
            self::DIESEL => 'Diesel',
            self::ELECTRIC => 'Elétrico',
        };
    }
}
