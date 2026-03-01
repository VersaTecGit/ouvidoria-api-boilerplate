<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;
use Modules\Common\Core\Support\Modules;
use Modules\Common\Logs\Support\AccessActions;
use Modules\Common\Logs\Support\AccessLogHelper;
use Modules\Transport\Filters\VehicleFilters;
use Modules\Transport\Models\Vehicle;
use Modules\Transport\Support\VehicleRequestStatus;

final readonly class FetchVehiclesList
{
    public function __construct(
        private VehicleFilters $filters,
    ) {}

    public function handle(DatatableDTO $dto): LengthAwarePaginator|Collection
    {
        $query = Vehicle::query()
            ->filtered($this->filters);

        $departureAt = request()->query('departure_at');
        $returnAt = request()->query('return_at');

        if ($departureAt && $returnAt) {
            $query->with(['vehicleRequests' => function ($q) use ($departureAt, $returnAt) {
                $q->where('status', VehicleRequestStatus::APPROVED->value)
                    ->where('departure_at', '<', $returnAt)
                    ->where('return_at', '>', $departureAt)
                    ->select(['id', 'vehicle_id', 'departure_at', 'return_at']);
            }]);
        }

        $query = Datatable::applyFilter($query, $dto, ['model', 'brand', 'plate']);
        $query = Datatable::applySort($query, $dto);

        if ($dto->log) {
            AccessLogHelper::log(
                action: AccessActions::DATATABLE_VIEW,
                module: Modules::VEHICLES,
                customMessage: AccessActions::DATATABLE_VIEW->message() . Modules::VEHICLES->description()
            );
        }

        return Datatable::applyPagination($query, $dto);
    }
}
