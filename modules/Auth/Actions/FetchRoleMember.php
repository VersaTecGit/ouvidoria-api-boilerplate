<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\ModelHasRole;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;

final readonly class FetchRoleMember
{
    public function handle(int $id, DatatableDTO $dto): LengthAwarePaginator|Collection
    {
        $query = ModelHasRole::query();
        $query = ModelHasRole::members($query, $id);
        $query = Datatable::applySort($query, $dto);

        return Datatable::applyPagination($query, $dto);
    }
}
