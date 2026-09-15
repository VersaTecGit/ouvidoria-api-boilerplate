<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;
use Modules\Ouvidoria\Filters\ManifestationFilters;
use Modules\Ouvidoria\Models\Manifestation;

final readonly class FetchManifestationsList
{
    public function __construct(
        private ManifestationFilters $filters,
    ) {}

    public function handle(DatatableDTO $dto): LengthAwarePaginator|Collection
    {
        $query = Manifestation::query()->with(['destinationAgency', 'unit']);

        $query->filtered($this->filters);

        $query = Datatable::applyFilter($query, $dto, [
            'protocol_number',
            'subject',
            'description',
            'occurrence_place',
        ]);

        // Newest first by default; an explicit client sort still wins.
        $query->orderByDesc('created_at');

        $query = Datatable::applySort($query, $dto);

        return Datatable::applyPagination($query, $dto);
    }
}
