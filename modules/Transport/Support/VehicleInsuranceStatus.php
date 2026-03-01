<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum VehicleInsuranceStatus: string
{
    case ACTIVE = 'active';
    case PENDING = 'pending';
    case EXPIRED = 'expired';
    case EXEMPT = 'exempt';

    public static function all(): array
    {
        return [
            self::ACTIVE->value,
            self::PENDING->value,
            self::EXPIRED->value,
            self::EXEMPT->value,
        ];
    }

    public static function toArray(): array
    {
        return array_column(VehicleInsuranceStatus::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::ACTIVE => 'Ativo',
            self::PENDING => 'Pendente',
            self::EXPIRED => 'Vencido',
            self::EXEMPT => 'Isento',
        };
    }
}
