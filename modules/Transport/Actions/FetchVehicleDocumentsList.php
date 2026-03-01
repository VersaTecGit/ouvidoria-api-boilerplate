<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;
use Modules\Transport\Filters\VehicleDocumentFilters;
use Modules\Transport\Models\VehicleDocument;

final readonly class FetchVehicleDocumentsList
{
    public function __construct(
        private FetchVehicle $fetchVehicle,
        private VehicleDocumentFilters $filters,
    ) {}

    public function handle(DatatableDTO $dto, string $uuid): LengthAwarePaginator|Collection
    {
        $vehicle = $this->fetchVehicle->handle($uuid);
        $query = VehicleDocument::query()->where('vehicle_id', $vehicle->id)->filtered($this->filters)->all();
        $query = Datatable::applySort($query, $dto);

        return Datatable::applyPagination($query, $dto);
    }
}
