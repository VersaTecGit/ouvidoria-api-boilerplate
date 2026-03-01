<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Modules\Auth\DTOs\SyncUserRolesDTO;
use Modules\Common\Core\Exceptions\ApiException;

final readonly class SyncUserRoles
{
    public function __construct(private FetchUser $fetchUser, private FetchValidateRoleHierarchy $fetchValidateRoleHierarchy) {}

    public function handle(string $user, SyncUserRolesDTO $dto): void
    {
        throw_if(! $this->fetchValidateRoleHierarchy->handle($dto->roles), new ApiException('Selecione apenas grupos que você tem acesso.'));

        $user = $this->fetchUser->handle($user);
        $user->syncRoles($dto->roles);

        if ($dto->extra_permissions) {
            $user->syncPermissions($dto->extra_permissions);
        }
    }
}
