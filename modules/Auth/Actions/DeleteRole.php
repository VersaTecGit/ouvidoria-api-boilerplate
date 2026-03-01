<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Modules\Auth\Support\DefaultRoles;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Exceptions\ApiException;

final readonly class DeleteRole
{
    public function __construct(private FetchRole $fetchRole, private FetchRoleMember $fetchRoleMember) {}

    public function handle(int $id): void
    {
        $role = $this->fetchRole->handle($id);

        throw_if($role->name === DefaultRoles::ADMIN->value, new ApiException('Não é permitido deletar o grupo ' . $role->descripiton . '.'));

        $dto = new DatatableDTO();
        $dto->per_page = 'all';
        $members = $this->fetchRoleMember->handle($role->id, $dto);

        if ($members->count() > 0) {
            throw new ApiException('Grupo não pode ser excluído por estar em uso.');
        }

        $role->delete();
    }
}
