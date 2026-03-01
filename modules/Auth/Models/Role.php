<?php

declare(strict_types=1);

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Builder;
use Modules\Auth\Exceptions\ProtectedRoleException;
use Modules\Auth\Support\DefaultRoles;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $nullable = ['description'];

    public static function nullable(): array
    {
        return (new self())->nullable;
    }

    public function isProtected(): bool
    {
        return in_array(
            $this->name,
            array_map(fn (DefaultRoles $role) => $role->value, DefaultRoles::protected())
        );
    }

    public function isHidden(): bool
    {
        return in_array(
            $this->name,
            array_map(fn (DefaultRoles $role) => $role->value, DefaultRoles::hidden())
        );
    }

    protected static function booted(): void
    {
        static::deleting(function (Role $role) {
            if ($role->isProtected()) {
                throw new ProtectedRoleException();
            }
        });

        static::updating(function (Role $role) {
            if ($role->isProtected()) {
                throw new ProtectedRoleException();
            }
        });

        static::addGlobalScope(
            'hidden',
            fn (Builder $builder) => $builder->whereNotIn('name', DefaultRoles::hidden())
        );
    }
}
