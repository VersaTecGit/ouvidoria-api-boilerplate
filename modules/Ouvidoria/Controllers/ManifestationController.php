<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;
use Modules\Ouvidoria\Actions\CreateManifestation;
use Modules\Ouvidoria\Actions\CreateManifestationLog;
use Modules\Ouvidoria\Actions\DeleteManifestation;
use Modules\Ouvidoria\Actions\FetchManifestation;
use Modules\Ouvidoria\Actions\FetchManifestationsList;
use Modules\Ouvidoria\Actions\RespondManifestation;
use Modules\Ouvidoria\Actions\UpdateManifestation;
use Modules\Ouvidoria\DTOs\CreateManifestationDTO;
use Modules\Ouvidoria\DTOs\CreateManifestationLogDTO;
use Modules\Ouvidoria\DTOs\RespondManifestationDTO;
use Modules\Ouvidoria\DTOs\UpdateManifestationDTO;
use Modules\Ouvidoria\Resources\ManifestationListResource;
use Modules\Ouvidoria\Resources\ManifestationLogResource;
use Modules\Ouvidoria\Resources\ManifestationResource;

/**
 * Staff-facing management of manifestations. Every route here sits behind
 * `auth` and a permission; the anonymous citizen endpoints are separate
 * (Fase 4) and must never reuse these resources.
 */
final class ManifestationController extends Controller
{
    public function index(DatatableDTO $dto, FetchManifestationsList $action): JsonResponse
    {
        return ManifestationListResource::collection($action->handle($dto))->response();
    }

    public function show(string $uuid, FetchManifestation $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new ManifestationResource($action->handle($uuid)));
    }

    public function store(CreateManifestationDTO $dto, CreateManifestation $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new ManifestationResource($action->handle($dto)),
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateManifestationDTO $dto, string $uuid, UpdateManifestation $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new ManifestationResource($action->handle($uuid, $dto)));
    }

    public function destroy(string $uuid, DeleteManifestation $action): NoContentResponse
    {
        $action->handle($uuid);

        return new NoContentResponse();
    }

    /**
     * Saves the attendant's opinion, emits the public timeline entry and
     * closes the manifestation as `respondida`.
     */
    public function respond(RespondManifestationDTO $dto, string $uuid, RespondManifestation $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new ManifestationResource($action->handle($uuid, $dto)));
    }

    public function storeLog(CreateManifestationLogDTO $dto, string $uuid, CreateManifestationLog $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new ManifestationLogResource($action->handle($uuid, $dto)),
            Response::HTTP_CREATED
        );
    }
}
