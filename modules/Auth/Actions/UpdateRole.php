<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Modules\Auth\DTOs\UpdateRoleDTO;
use Modules\Auth\Models\Role;
use Modules\Auth\Support\DefaultRoles;
use Modules\Common\Core\Exceptions\ApiException;

final readonly class UpdateRole
{
    public function __construct(private FetchRole $fetchRole) {}

    public function handle(int $id, UpdateRoleDTO $dto): Role
    {
        $role = $this->fetchRole->handle($id);

        if (isset($dto->name)) {
            throw_if($role->name === DefaultRoles::ADMIN->value, new ApiException('Não é permitido alterar o nome do grupo ' . $role->descripiton . '.'));
        }

        $role->fill($dto->nullableSafeToArray(Role::nullable()));
        $role->save();

        return $role;
    }
}
