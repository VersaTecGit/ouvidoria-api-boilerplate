<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Illuminate\Database\Eloquent\Collection;
use Modules\Auth\Models\Permission;
use Modules\Auth\Support\DefaultRoles;
use Modules\Auth\Support\PermissionGroups;
use Modules\Auth\Support\Permissions;

final readonly class FetchPermissionsModulesList
{
    public function handle(): array
    {
        $permissions = Permission::query()->get();

        return [
            'modules' => $this->getModules($permissions),
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Modules\Auth\Models\Permission>  $permissions
     */
    private function getModules(Collection $permissions): array
    {
        $modules = PermissionGroups::modules();

        $permissionsByGroup = [];

        foreach ($permissions as $permission) {
            $group = PermissionGroups::fromPermission($permission->name)->value;

            $permissionsByGroup[$group][] = $permission;
        }

        $roles = DefaultRoles::all();

        $rolesByPermission = [];

        foreach ($roles as $role) {
            foreach ($role->permissions() as $p) {
                $rolesByPermission[$p->value][] = $role->value;
            }
        }

        foreach ($modules as $mKey => $module) {
            foreach ($module['groups'] as $gKey => $groupInfo) {

                $completeKey = $module['name'] . ':' . $groupInfo['name'];

                $groupPermissions = $permissionsByGroup[$completeKey] ?? [];

                $modules[$mKey]['groups'][$gKey] = [
                    'name' => $groupInfo['name'],
                    'description' => $groupInfo['description'],
                    'permissions' => array_map(function ($permission) use ($rolesByPermission) {
                        $permissionEnum = Permissions::tryFrom($permission->name);

                        return [
                            'name' => $permission->name,
                            'description' => $permission->description,
                            'detail' => $permissionEnum?->detail(),
                            'default_roles' => $rolesByPermission[$permission->name] ?? [],
                        ];
                    }, $groupPermissions),
                ];
            }
        }

        return $modules;
    }
}
