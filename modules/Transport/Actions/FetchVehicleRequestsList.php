<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Auth\Actions\LoggedUser;
use Modules\Auth\Support\Permissions;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;
use Modules\Common\Core\Support\Modules;
use Modules\Common\Logs\Support\AccessActions;
use Modules\Common\Logs\Support\AccessLogHelper;
use Modules\Transport\Filters\VehicleRequestFilters;
use Modules\Transport\Models\VehicleRequest;

final readonly class FetchVehicleRequestsList
{
    public function __construct(
        private LoggedUser $loggedUser,
        private VehicleRequestFilters $filters,
    ) {}

    public function handle(DatatableDTO $dto): LengthAwarePaginator|Collection
    {
        $loggedUser = $this->loggedUser->handle();

        $query = VehicleRequest::query()->with([
            'requester',
            'vehicle',
            'driver',
            'userPassengers',
        ])->filtered($this->filters);

        if (! $loggedUser->hasPermissionTo(Permissions::LIST_VEHICLE_REQUESTS->value)) {
            $query->where(function ($q) use ($loggedUser) {
                $q->where('requester_id', $loggedUser->id)
                    ->orWhere('driver_id', $loggedUser->id)
                    ->orWhereHas('userPassengers', function ($q2) use ($loggedUser) {
                        $q2->where('id', $loggedUser->id);
                    });
            });
        }

        $query = Datatable::applyFilter($query, $dto, ['protocol_number']);
        $query = Datatable::applySort($query, $dto);

        if ($dto->log) {
            AccessLogHelper::log(
                action: AccessActions::DATATABLE_VIEW,
                module: Modules::VEHICLE_REQUESTS,
                customMessage: AccessActions::DATATABLE_VIEW->message() . Modules::VEHICLE_REQUESTS->description()
            );
        }

        return Datatable::applyPagination($query, $dto);
    }
}
