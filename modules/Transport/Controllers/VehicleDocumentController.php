<?php

declare(strict_types=1);

namespace Modules\Transport\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;
use Modules\Transport\Actions\CreateVehicleDocument;
use Modules\Transport\Actions\DeleteVehicleDocument;
use Modules\Transport\Actions\FetchVehicleDocument;
use Modules\Transport\Actions\FetchVehicleDocumentsList;
use Modules\Transport\Actions\UpdateVehicleDocument;
use Modules\Transport\DTOs\CreateVehicleDocumentDTO;
use Modules\Transport\DTOs\UpdateVehicleDocumentDTO;
use Modules\Transport\Resources\VehicleDocumentResource;

final class VehicleDocumentController extends Controller
{
    public function index(DatatableDTO $dto, string $uuid, FetchVehicleDocumentsList $action): JsonResponse
    {
        return VehicleDocumentResource::collection($action->handle($dto, $uuid))->response();
    }

    public function show(string $uuid, FetchVehicleDocument $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new VehicleDocumentResource($action->handle($uuid)));
    }

    public function store(CreateVehicleDocumentDTO $dto, string $uuid, CreateVehicleDocument $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new VehicleDocumentResource($action->handle($dto, $uuid)),
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateVehicleDocumentDTO $dto, string $uuid, UpdateVehicleDocument $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new VehicleDocumentResource($action->handle($uuid, $dto)));
    }

    public function destroy(string $uuid, DeleteVehicleDocument $action): NoContentResponse
    {
        $action->handle($uuid);

        return new NoContentResponse();
    }
}
