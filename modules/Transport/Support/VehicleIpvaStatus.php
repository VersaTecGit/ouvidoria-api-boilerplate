<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum VehicleIpvaStatus: string
{
    case PAID = 'paid';
    case PENDING = 'pending';
    case OVERDUE = 'overdue';
    case EXEMPT = 'exempt';

    public static function all(): array
    {
        return [
            self::PAID->value,
            self::PENDING->value,
            self::OVERDUE->value,
            self::EXEMPT->value,
        ];
    }

    public static function toArray(): array
    {
        return array_column(VehicleIpvaStatus::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::PAID => 'Pago',
            self::PENDING => 'Pendente',
            self::OVERDUE => 'Vencido',
            self::EXEMPT => 'Isento',
        };
    }
}
