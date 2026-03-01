<?php

declare(strict_types=1);

namespace Modules\Auth\Support;

enum DefaultRoles: string
{
    case SUPER_ADMIN = 'super-admin';
    case ADMIN = 'admin';

    public static function all(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
        ];
    }

    public static function hidden(): array
    {
        return [
            self::SUPER_ADMIN,
        ];
    }

    public static function protected(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
        ];
    }

    public function description(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrador',
            self::ADMIN => 'Administrador',
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::ADMIN => Permissions::all(),
            default => []
        };
    }
}
