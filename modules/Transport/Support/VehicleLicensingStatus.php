<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum VehicleLicensingStatus: string
{
    case VALID = 'valid';
    case PENDING = 'pending';
    case EXPIRED = 'expired';
    case EXEMPT = 'exempt';

    public static function all(): array
    {
        return [
            self::VALID->value,
            self::PENDING->value,
            self::EXPIRED->value,
            self::EXEMPT->value,
        ];
    }

    public static function toArray(): array
    {
        return array_column(VehicleLicensingStatus::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::VALID => 'Válido',
            self::PENDING => 'Pendente',
            self::EXPIRED => 'Vencido',
            self::EXEMPT => 'Isento',
        };
    }
}
