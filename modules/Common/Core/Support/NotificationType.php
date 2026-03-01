<?php

declare(strict_types=1);

namespace Modules\Common\Core\Support;

enum NotificationType: string
{
    case RELEASE_NOTES = 'release-notes';
    case DRIVER_CNH_EXPIRATION = 'driver-cnh-expiration';
    case VEHICLE_REQUEST_REJECTED = 'vehicle-request-rejected';
    case VEHICLE_REQUEST_APPROVED = 'vehicle-request-approved';

    public static function all(): array
    {
        return [
            self::RELEASE_NOTES,
            self::DRIVER_CNH_EXPIRATION,
            self::VEHICLE_REQUEST_REJECTED,
            self::VEHICLE_REQUEST_APPROVED,
        ];
    }

    public static function toArray(): array
    {
        return array_column(NotificationType::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::RELEASE_NOTES => 'Notificação de novas notas de versão',
            self::DRIVER_CNH_EXPIRATION => 'Notificação de CNH do motorista prestes a vencer',
            self::VEHICLE_REQUEST_REJECTED => 'Notificação de solicitação de veículo rejeitada',
            self::VEHICLE_REQUEST_APPROVED => 'Notificação de solicitação de veículo aprovada',
        };
    }
}
