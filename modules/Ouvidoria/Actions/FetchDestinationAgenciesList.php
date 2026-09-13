<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;
use Modules\Ouvidoria\Filters\DestinationAgencyFilters;
use Modules\Ouvidoria\Models\DestinationAgency;

final readonly class FetchDestinationAgenciesList
{
    public function __construct(
        private DestinationAgencyFilters $filters,
    ) {}

    /**
     * @param  bool  $onlyActive  When true, keeps the `active-destination-agencies`
     *                            global scope, which is what the public endpoint must always use.
     */
    public function handle(DatatableDTO $dto, bool $onlyActive = false): LengthAwarePaginator|Collection
    {
        $query = DestinationAgency::query();

        if (! $onlyActive) {
            $query->withoutGlobalScope('active-destination-agencies');
        }

        $query->filtered($this->filters);

        $query = Datatable::applyFilter($query, $dto, ['name']);

        // Curated combo order; overridden when the caller asks for an explicit sort.
        $query->orderBy('order')->orderBy('name');

        $query = Datatable::applySort($query, $dto);

        return Datatable::applyPagination($query, $dto);
    }
}
