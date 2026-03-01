<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum VehicleMaintenanceStatus: string
{
    case SCHEDULED = 'scheduled';
    case DONE = 'done';
    case CANCELED = 'canceled';

    public static function all(): array
    {
        return [
            self::SCHEDULED->value,
            self::DONE->value,
            self::CANCELED->value,
        ];
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Agendada',
            self::DONE => 'Concluída',
            self::CANCELED => 'Cancelada',
        };
    }
}
