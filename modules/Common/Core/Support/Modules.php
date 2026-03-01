<?php

declare(strict_types=1);

namespace Modules\Common\Core\Support;

enum Modules: string
{
    case AUTH = 'auth';
    case USERS = 'users';
    case USER_LOGINS = 'user_logins';
    case ROLES = 'roles';
    case ACCESS_LOGS = 'access_logs';
    case QUESTIONNAIRES = 'questionnaires';
    case VEHICLES = 'vehicles';
    case VEHICLE_REQUESTS = 'vehicle_requests';
    case VEHICLE_TRIPS = 'vehicle_trips';

    public static function all(): array
    {
        return [
            self::AUTH,
            self::USERS,
            self::USER_LOGINS,
            self::ROLES,
            self::ACCESS_LOGS,
            self::QUESTIONNAIRES,
            self::VEHICLES,
            self::VEHICLE_REQUESTS,
            self::VEHICLE_TRIPS,
        ];
    }

    public static function toArray(): array
    {
        return array_column(Modules::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::AUTH => 'Autenticação',
            self::USERS => 'Usuários',
            self::USER_LOGINS => 'Logins de usuários',
            self::ROLES => 'Grupos',
            self::ACCESS_LOGS => 'Logs de acesso',
            self::QUESTIONNAIRES => 'Questionários',
            self::VEHICLES => 'Veículos',
            self::VEHICLE_REQUESTS => 'Solicitações de veículos',
            self::VEHICLE_TRIPS => 'Histórico de viagens',
        };
    }
}
