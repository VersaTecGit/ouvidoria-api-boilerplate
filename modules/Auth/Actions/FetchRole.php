<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Modules\Auth\Models\Role;

final readonly class FetchRole
{
    public function handle(int $id): Role
    {
        return Role::findOrFail($id);
    }
}
