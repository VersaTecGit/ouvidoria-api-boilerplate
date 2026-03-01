<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;
use Modules\Common\Core\Support\Modules;
use Modules\Common\Logs\Support\AccessActions;
use Modules\Common\Logs\Support\AccessLogHelper;
use Modules\Transport\Filters\VehicleTripFilters;
use Modules\Transport\Models\VehicleTrip;

final readonly class FetchVehicleTripsList
{
    public function __construct(
        private VehicleTripFilters $filters,
    ) {}

    public function handle(DatatableDTO $dto): LengthAwarePaginator|Collection
    {
        $query = VehicleTrip::query()->with([
            'vehicleRequest',
            'vehicleRequest.requester',
            'vehicleRequest.vehicle',
            'vehicleRequest.driver',
            'vehicleRequest.userPassengers',
            'userPassengers',
        ])->filtered($this->filters);

        $query = $this->applyFilter($query, $dto);
        $query = Datatable::applySort($query, $dto);

        if ($dto->log) {
            AccessLogHelper::log(
                action: AccessActions::DATATABLE_VIEW,
                module: Modules::VEHICLE_TRIPS,
                customMessage: AccessActions::DATATABLE_VIEW->message() . Modules::VEHICLE_TRIPS->description()
            );
        }

        return Datatable::applyPagination($query, $dto);
    }

    public function applyFilter(Builder $builder, DatatableDTO $dto): Builder
    {
        if (empty($dto->search)) {
            return $builder;
        }

        return $builder->whereHas('vehicleRequest', function (Builder $query) use ($dto) {
            $query->whereRaw("unaccent(protocol_number) ilike unaccent('%{$dto->search}%')");
        });
    }
}
