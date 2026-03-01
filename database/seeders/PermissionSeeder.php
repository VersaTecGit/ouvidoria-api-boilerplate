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
        return collect(Permissions::all())->map(fn (Permissions $permission) => [
            'name' => $permission->value,
            'description' => $permission->description(),
        ]);
    }

    private function createPermissions(Collection $permissions): void
    {
        $model = app(config('permission.models.permission'));

        $permissions->each(function (array $permission) use ($model) {
            $model::updateOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description']]
            );
        });
    }

    private function getRoles(): Collection
    {
        return collect(DefaultRoles::all())->map(fn (DefaultRoles $role) => [
            'name' => $role->value,
            'description' => $role->description(),
        ]);
    }

    private function createRoles(Collection $roles): void
    {
        $model = app(config('permission.models.role'));

        $roles->each(function (array $role) use ($model) {
            $model::withoutGlobalScopes()->updateOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        });
    }

    private function assignPermissionsToRoles(): void
    {
        $roles = $this->getRoles()->reject(fn ($r) => $r['name'] === DefaultRoles::SUPER_ADMIN->value);

        $roles->each(function (array $roleData) {
            $role = app(config('permission.models.role'))::where('name', $roleData['name'])->first();
            $permissions = collect(DefaultRoles::from($role->name)->permissions())
                ->map(fn (Permissions $perm) => app(config('permission.models.permission'))::where('name', $perm->value)->first())
                ->filter();

            $role->syncPermissions($permissions);
        });
    }

    private function assignRolesToUsers(): void
    {
        if ($user = User::where('login', 'admin')->first()) {
            $user->assignRole(DefaultRoles::ADMIN->value);
        }
    }
}
