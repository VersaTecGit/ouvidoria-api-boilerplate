<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Modules\Auth\Models\User;
use Modules\Auth\Support\DefaultRoles;
use Modules\Auth\Support\Permissions;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createPermissions($this->getPermissions());

        $this->createRoles($this->getRoles());

        $this->assignPermissionsToRoles();

        $this->assignRolesToUsers();
    }

    private function getPermissions(): Collection
    {
        return collect(Permissions::all())
            ->transform(function (Permissions $permission) {
                return [
                    'name' => $permission->value,
                    'description' => $permission->description(),
                ];
            });
    }

    private function createPermissions(Collection $permissions): void
    {
        $permissions->each(function (array $permission) {
            app(config('permission.models.permission'))->firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => config('auth.defaults.guard', 'api')],
                ['description' => $permission['description']]
            );
        });
    }

    private function getRoles(): Collection
    {
        return collect(DefaultRoles::all())
            ->transform(function (DefaultRoles $role) {
                return [
                    'name' => $role->value,
                    'description' => $role->description(),
                ];
            });
    }

    private function createRoles(Collection $roles): void
    {
        $roles->each(function (array $role) {
            app(config('permission.models.role'))->withoutGlobalScopes()->firstOrCreate(
                ['name' => $role['name'], 'guard_name' => config('auth.defaults.guard', 'api')],
                ['description' => $role['description']]
            );
        });
    }

    private function assignPermissionsToRoles(): void
    {
        $roles = $this->getRoles()->filter(fn(array $role) => $role['name'] !== DefaultRoles::SUPER_ADMIN->value);

        $roles->each(function (array $role) {
            $role = app(config('permission.models.role'))->where('name', $role['name'])->first();

            $permissions = DefaultRoles::from($role['name'])->permissions();

            $permissions = collect($permissions)
                ->map(fn(Permissions $permission) => app(config('permission.models.permission'))->where('name', $permission->value)->first());

            $role->givePermissionTo($permissions);
        });
    }

    private function assignRolesToUsers(): void
    {
        $User = User::where('login', DefaultRoles::ADMIN->value)->first();
        $User->assignRole(DefaultRoles::ADMIN->value);
    }
}
