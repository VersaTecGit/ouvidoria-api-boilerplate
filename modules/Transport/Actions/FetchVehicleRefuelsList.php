<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;
use Modules\Transport\Filters\VehicleRefuelFilters;
use Modules\Transport\Models\VehicleRefuel;

final readonly class FetchVehicleRefuelsList
{
    public function __construct(
        private FetchVehicle $fetchVehicle,
        private VehicleRefuelFilters $filters,
    ) {}

    public function handle(DatatableDTO $dto, string $uuid): LengthAwarePaginator|Collection
    {
        $vehicle = $this->fetchVehicle->handle($uuid);
        $query = VehicleRefuel::query()->where('vehicle_id', $vehicle->id)->filtered($this->filters)->all();
        $query = Datatable::applySort($query, $dto);

        return Datatable::applyPagination($query, $dto);
    }
}
