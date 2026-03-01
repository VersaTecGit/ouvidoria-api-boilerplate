<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum VehicleMaintenanceService: string
{
    case OIL_CHANGE = 'oil_change';
    case TIRE_REPLACEMENT = 'tire_replacement';
    case BRAKE_SERVICE = 'brake_service';
    case GENERAL_INSPECTION = 'general_inspection';
    case OTHER = 'other';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        return self::all();
    }

    public function description(): string
    {
        return match ($this) {
            self::OIL_CHANGE => 'Troca de óleo',
            self::TIRE_REPLACEMENT => 'Troca de pneus',
            self::BRAKE_SERVICE => 'Serviço de freios',
            self::GENERAL_INSPECTION => 'Inspeção geral',
            self::OTHER => 'Outro',
        };
    }
}
