<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum VehicleType: string
{
    case CAR = 'car';
    case MOTORCYCLE = 'motorcycle';
    case VAN = 'van';
    case PICKUP_TRUCK = 'pickup_truck';
    case MINIBUS = 'minibus';
    case BUS = 'bus';
    case OTHER = 'other';

    public static function all(): array
    {
        return [
            self::CAR->value,
            self::MOTORCYCLE->value,
            self::VAN->value,
            self::PICKUP_TRUCK->value,
            self::MINIBUS->value,
            self::BUS->value,
            self::OTHER->value,
        ];
    }

    public static function toArray(): array
    {
        return array_column(VehicleType::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::CAR => 'Carro',
            self::MOTORCYCLE => 'Motocicleta',
            self::VAN => 'Van',
            self::PICKUP_TRUCK => 'Caminhonete',
            self::MINIBUS => 'Micro-ônibus',
            self::BUS => 'Ônibus',
            self::OTHER => 'Outro',
        };
    }
}
