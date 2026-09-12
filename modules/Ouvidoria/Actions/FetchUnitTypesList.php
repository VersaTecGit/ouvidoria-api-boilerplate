<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;
use Modules\Ouvidoria\Models\UnitType;

final readonly class FetchUnitTypesList
{
    public function handle(DatatableDTO $dto): LengthAwarePaginator|Collection
    {
        $query = UnitType::query();

        $query = Datatable::applyFilter($query, $dto, ['name']);
        $query = Datatable::applySort($query, $dto);

        return Datatable::applyPagination($query, $dto);
    }
}
