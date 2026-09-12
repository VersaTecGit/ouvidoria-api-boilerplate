<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;
use Modules\Ouvidoria\Filters\UnitFilters;
use Modules\Ouvidoria\Models\Unit;

final readonly class FetchUnitsList
{
    public function __construct(
        private UnitFilters $filters,
    ) {}

    /**
     * @param  bool  $onlyActive  When true, keeps the `active-units` global scope,
     *                            which is what the public endpoint must always use.
     */
    public function handle(DatatableDTO $dto, bool $onlyActive = false): LengthAwarePaginator|Collection
    {
        $query = Unit::query()->with('unitType');

        if (! $onlyActive) {
            $query->withoutGlobalScope('active-units');
        }

        $query->filtered($this->filters);

        $query = Datatable::applyFilter($query, $dto, ['name', 'code']);
        $query = Datatable::applySort($query, $dto);

        return Datatable::applyPagination($query, $dto);
    }
}
