<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\Permission;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;

final readonly class FetchPermissionsList
{
    public function handle(DatatableDTO $dto): LengthAwarePaginator|Collection
    {
        $query = $this->validateFeaturesAreActive(Permission::query());

        $query = Datatable::applyFilter($query, $dto, ['name', 'description']);
        $query = Datatable::applySort($query, $dto);

        return Datatable::applyPagination($query, $dto);
    }

    private function validateFeaturesAreActive(Builder $builder): Builder
    {
        $modules = tenant()->modules->toArray();

        $builder->where(function (Builder $query) use ($modules) {
            foreach ($modules as $module) {
                $prefix = strtoupper($module);
                $query->orwhere('name', 'LIKE', "{$prefix}%");
            }
            $query->orwhere('name', 'LIKE', 'ALL%');
        });

        return $builder;
    }
}
