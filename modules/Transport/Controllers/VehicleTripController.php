<?php

declare(strict_types=1);

namespace Modules\Transport\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;
use Modules\Transport\Actions\DeleteVehicleTrip;
use Modules\Transport\Actions\FetchVehicleTrip;
use Modules\Transport\Actions\FetchVehicleTripsList;
use Modules\Transport\Actions\UpdateVehicleTrip;
use Modules\Transport\DTOs\UpdateVehicleTripDTO;
use Modules\Transport\Resources\VehicleTripResource;

final class VehicleTripController extends Controller
{
    public function index(DatatableDTO $dto, FetchVehicleTripsList $action): JsonResponse
    {
        return VehicleTripResource::collection($action->handle($dto))->response();
    }

    public function show(string $uuid, FetchVehicleTrip $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new VehicleTripResource($action->handle($uuid)));
    }

    public function update(UpdateVehicleTripDTO $dto, string $uuid, UpdateVehicleTrip $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new VehicleTripResource($action->handle($uuid, $dto)));
    }

    public function destroy(string $uuid, DeleteVehicleTrip $action): NoContentResponse
    {
        $action->handle($uuid);

        return new NoContentResponse();
    }
}
