<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Illuminate\Database\Eloquent\Collection;

final readonly class FetchRolePermissionsList
{
    public function __construct(private FetchRole $fetchRole) {}

    public function handle(int $id): Collection
    {
        $role = $this->fetchRole->handle($id);

        return $role->permissions;
    }
}
