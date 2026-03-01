<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum VehicleTripStatus: string
{
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';

    public static function all(): array
    {
        return [
            self::NOT_STARTED->value,
            self::IN_PROGRESS->value,
            self::COMPLETED->value,
            self::CANCELED->value,
        ];
    }

    public static function toArray(): array
    {
        return array_column(VehicleTripStatus::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'Não Iniciado',
            self::IN_PROGRESS => 'Em Progresso',
            self::COMPLETED => 'Concluído',
            self::CANCELED => 'Cancelado',
        };
    }
}
