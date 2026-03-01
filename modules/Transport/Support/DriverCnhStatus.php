<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum DriverCnhStatus: string
{
    case VALID = 'valid';
    case EXPIRED = 'expired';
    case SUSPENDED = 'suspended';

    public static function all(): array
    {
        return [
            self::VALID,
            self::EXPIRED,
            self::SUSPENDED,
        ];
    }

    public static function toArray(): array
    {
        return array_column(DriverCnhStatus::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::VALID => 'CNH Válida',
            self::EXPIRED => 'CNH Vencida',
            self::SUSPENDED => 'CNH Suspensa',
        };
    }
}
