<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum VehicleRequestPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public static function all(): array
    {
        return [
            self::LOW->value,
            self::MEDIUM->value,
            self::HIGH->value,
            self::CRITICAL->value,
        ];
    }

    public static function toArray(): array
    {
        return array_column(VehicleRequestPriority::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::LOW => 'Baixa',
            self::MEDIUM => 'Média',
            self::HIGH => 'Alta',
            self::CRITICAL => 'Emergência',
        };
    }
}
