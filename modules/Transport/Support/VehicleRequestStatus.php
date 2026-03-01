<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum VehicleRequestStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELED = 'canceled';

    public static function all(): array
    {
        return [
            self::PENDING->value,
            self::APPROVED->value,
            self::REJECTED->value,
            self::CANCELED->value,
        ];
    }

    public static function toArray(): array
    {
        return array_column(VehicleRequestStatus::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::APPROVED => 'Aprovado',
            self::REJECTED => 'Rejeitado',
            self::CANCELED => 'Cancelado',
        };
    }
}
