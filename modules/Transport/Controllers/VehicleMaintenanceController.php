<?php

declare(strict_types=1);

namespace Modules\Transport\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;
use Modules\Transport\Actions\CreateVehicleMaintenance;
use Modules\Transport\Actions\DeleteVehicleMaintenance;
use Modules\Transport\Actions\FetchVehicleMaintenance;
use Modules\Transport\Actions\FetchVehicleMaintenancesList;
use Modules\Transport\Actions\UpdateVehicleMaintenance;
use Modules\Transport\DTOs\CreateVehicleMaintenanceDTO;
use Modules\Transport\DTOs\UpdateVehicleMaintenanceDTO;
use Modules\Transport\Resources\VehicleMaintenanceResource;

final class VehicleMaintenanceController extends Controller
{
    public function index(DatatableDTO $dto, string $uuid, FetchVehicleMaintenancesList $action): JsonResponse
    {
        return VehicleMaintenanceResource::collection($action->handle($dto, $uuid))->response();
    }

    public function show(string $uuid, FetchVehicleMaintenance $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new VehicleMaintenanceResource($action->handle($uuid)));
    }

    public function store(CreateVehicleMaintenanceDTO $dto, string $uuid, CreateVehicleMaintenance $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new VehicleMaintenanceResource($action->handle($dto, $uuid)),
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateVehicleMaintenanceDTO $dto, string $uuid, UpdateVehicleMaintenance $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new VehicleMaintenanceResource($action->handle($uuid, $dto)));
    }

    public function destroy(string $uuid, DeleteVehicleMaintenance $action): NoContentResponse
    {
        $action->handle($uuid);

        return new NoContentResponse();
    }
}
