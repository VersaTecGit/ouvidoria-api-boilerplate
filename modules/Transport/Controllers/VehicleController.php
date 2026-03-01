<?php

declare(strict_types=1);

namespace Modules\Transport\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;
use Modules\Transport\Actions\CreateVehicle;
use Modules\Transport\Actions\DeleteVehicle;
use Modules\Transport\Actions\FetchVehicle;
use Modules\Transport\Actions\FetchVehiclesList;
use Modules\Transport\Actions\UpdateVehicle;
use Modules\Transport\DTOs\CreateVehicleDTO;
use Modules\Transport\DTOs\UpdateVehicleDTO;
use Modules\Transport\Resources\VehicleResource;

final class VehicleController extends Controller
{
    public function index(DatatableDTO $dto, FetchVehiclesList $action): JsonResponse
    {
        return VehicleResource::collection($action->handle($dto))->response();
    }

    public function show(string $uuid, FetchVehicle $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new VehicleResource($action->handle($uuid)));
    }

    public function store(CreateVehicleDTO $dto, CreateVehicle $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new VehicleResource($action->handle($dto)),
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateVehicleDTO $dto, string $uuid, UpdateVehicle $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new VehicleResource($action->handle($uuid, $dto)));
    }

    public function destroy(string $uuid, DeleteVehicle $action): NoContentResponse
    {
        $action->handle($uuid);

        return new NoContentResponse();
    }
}
