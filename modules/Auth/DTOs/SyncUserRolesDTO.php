<?php

declare(strict_types=1);

namespace Modules\Auth\DTOs;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

final class SyncUserRolesDTO extends ValidatedDTO
{
    public array $roles;

    public array $extra_permissions;

    protected function rules(): array
    {
        return [
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
            'extra_permissions' => ['sometimes', 'array'],
            'extra_permissions.*' => ['exists:permissions,name'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [];
    }
}
