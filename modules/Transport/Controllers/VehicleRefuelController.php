<?php

declare(strict_types=1);

namespace Modules\Transport\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;
use Modules\Transport\Actions\CreateVehicleRefuel;
use Modules\Transport\Actions\DeleteVehicleRefuel;
use Modules\Transport\Actions\FetchVehicleRefuel;
use Modules\Transport\Actions\FetchVehicleRefuelsList;
use Modules\Transport\Actions\UpdateVehicleRefuel;
use Modules\Transport\DTOs\CreateVehicleRefuelDTO;
use Modules\Transport\DTOs\UpdateVehicleRefuelDTO;
use Modules\Transport\Resources\VehicleRefuelResource;

final class VehicleRefuelController extends Controller
{
    public function index(DatatableDTO $dto, string $uuid, FetchVehicleRefuelsList $action): JsonResponse
    {
        return VehicleRefuelResource::collection($action->handle($dto, $uuid))->response();
    }

    public function show(string $uuid, FetchVehicleRefuel $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new VehicleRefuelResource($action->handle($uuid)));
    }

    public function store(CreateVehicleRefuelDTO $dto, string $uuid, CreateVehicleRefuel $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new VehicleRefuelResource($action->handle($dto, $uuid)),
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateVehicleRefuelDTO $dto, string $uuid, UpdateVehicleRefuel $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new VehicleRefuelResource($action->handle($uuid, $dto)));
    }

    public function destroy(string $uuid, DeleteVehicleRefuel $action): NoContentResponse
    {
        $action->handle($uuid);

        return new NoContentResponse();
    }
}
