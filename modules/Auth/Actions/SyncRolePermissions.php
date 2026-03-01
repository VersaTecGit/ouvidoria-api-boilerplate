<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Modules\Auth\DTOs\SyncRolePermissionsDTO;
use Modules\Common\Core\Exceptions\ApiException;

final readonly class SyncRolePermissions
{
    public function __construct(private FetchRole $fetchRole, private FetchValidateRoleHierarchy $fetchValidateRoleHierarchy) {}

    public function handle(int $id, SyncRolePermissionsDTO $dto): void
    {
        throw_if(! $this->fetchValidateRoleHierarchy->handle($id), new ApiException('Selecione apenas grupos que você tem acesso.'));

        $role = $this->fetchRole->handle($id);
        $role->syncPermissions($dto->permissions);
    }
}
