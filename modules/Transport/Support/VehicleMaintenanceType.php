<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum VehicleMaintenanceType: string
{
    case PREVENTIVE = 'preventive';
    case CORRECTIVE = 'corrective';

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
            self::PREVENTIVE => 'Preventiva',
            self::CORRECTIVE => 'Corretiva',
        };
    }
}
