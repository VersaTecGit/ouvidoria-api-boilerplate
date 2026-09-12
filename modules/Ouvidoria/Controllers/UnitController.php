<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;
use Modules\Ouvidoria\Actions\CreateUnit;
use Modules\Ouvidoria\Actions\DeleteUnit;
use Modules\Ouvidoria\Actions\FetchUnit;
use Modules\Ouvidoria\Actions\FetchUnitsList;
use Modules\Ouvidoria\Actions\UpdateUnit;
use Modules\Ouvidoria\DTOs\CreateUnitDTO;
use Modules\Ouvidoria\DTOs\UpdateUnitDTO;
use Modules\Ouvidoria\Resources\PublicUnitResource;
use Modules\Ouvidoria\Resources\UnitResource;

final class UnitController extends Controller
{
    public function index(DatatableDTO $dto, FetchUnitsList $action): JsonResponse
    {
        return UnitResource::collection($action->handle($dto))->response();
    }

    /**
     * Unauthenticated listing used by the public manifestation form.
     * Only active units, through the minimal public resource.
     */
    public function publicIndex(DatatableDTO $dto, FetchUnitsList $action): JsonResponse
    {
        return PublicUnitResource::collection(
            $action->handle($dto, onlyActive: true)
        )->response();
    }

    public function show(string $uuid, FetchUnit $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new UnitResource($action->handle($uuid)));
    }

    public function store(CreateUnitDTO $dto, CreateUnit $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new UnitResource($action->handle($dto)),
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateUnitDTO $dto, string $uuid, UpdateUnit $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new UnitResource($action->handle($uuid, $dto)));
    }

    public function destroy(string $uuid, DeleteUnit $action): NoContentResponse
    {
        $action->handle($uuid);

        return new NoContentResponse();
    }
}
