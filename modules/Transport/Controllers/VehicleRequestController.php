<?php

declare(strict_types=1);

namespace Modules\Transport\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;
use Modules\Transport\Actions\CreateVehicleRequest;
use Modules\Transport\Actions\DeleteVehicleRequest;
use Modules\Transport\Actions\FetchVehicleRequest;
use Modules\Transport\Actions\FetchVehicleRequestsList;
use Modules\Transport\Actions\UpdateVehicleRequest;
use Modules\Transport\DTOs\CreateVehicleRequestDTO;
use Modules\Transport\DTOs\UpdateVehicleRequestDTO;
use Modules\Transport\Resources\VehicleRequestResource;

final class VehicleRequestController extends Controller
{
    public function index(DatatableDTO $dto, FetchVehicleRequestsList $action): JsonResponse
    {
        return VehicleRequestResource::collection($action->handle($dto))->response();
    }

    public function show(string $uuid, FetchVehicleRequest $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new VehicleRequestResource($action->handle($uuid)));
    }

    public function store(CreateVehicleRequestDTO $dto, CreateVehicleRequest $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new VehicleRequestResource($action->handle($dto)),
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateVehicleRequestDTO $dto, string $uuid, UpdateVehicleRequest $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new VehicleRequestResource($action->handle($uuid, $dto)));
    }

    public function destroy(string $uuid, DeleteVehicleRequest $action): NoContentResponse
    {
        $action->handle($uuid);

        return new NoContentResponse();
    }
}
