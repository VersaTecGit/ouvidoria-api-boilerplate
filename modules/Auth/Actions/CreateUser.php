<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\DTOs\CreateUserDTO;
use Modules\Auth\Models\User;

final readonly class CreateUser
{
    public function handle(CreateUserDTO $dto): User
    {
        try {
            DB::beginTransaction();

            $user = $dto->toModel(User::class);
            $user->password = Hash::make($dto->password);
            $user->save();

            $user->assignRole($dto->role);

            if ($dto->extra_permissions) {
                $user->givePermissionTo($dto->extra_permissions);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $user;
    }
}
