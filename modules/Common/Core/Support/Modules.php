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

    public static function all(): array
    {
        return [
            self::AUTH,
            self::USERS,
            self::USER_LOGINS,
            self::ROLES,
            self::ACCESS_LOGS,
            self::QUESTIONNAIRES,
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
        };
    }
}
