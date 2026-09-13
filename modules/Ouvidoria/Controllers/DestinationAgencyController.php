<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;
use Modules\Ouvidoria\Actions\CreateDestinationAgency;
use Modules\Ouvidoria\Actions\DeleteDestinationAgency;
use Modules\Ouvidoria\Actions\FetchDestinationAgenciesList;
use Modules\Ouvidoria\Actions\FetchDestinationAgency;
use Modules\Ouvidoria\Actions\UpdateDestinationAgency;
use Modules\Ouvidoria\DTOs\CreateDestinationAgencyDTO;
use Modules\Ouvidoria\DTOs\UpdateDestinationAgencyDTO;
use Modules\Ouvidoria\Resources\DestinationAgencyResource;
use Modules\Ouvidoria\Resources\PublicDestinationAgencyResource;

final class DestinationAgencyController extends Controller
{
    public function index(DatatableDTO $dto, FetchDestinationAgenciesList $action): JsonResponse
    {
        return DestinationAgencyResource::collection($action->handle($dto))->response();
    }

    /**
     * Unauthenticated listing used by the public manifestation form.
     * Only active agencies, through the minimal public resource.
     */
    public function publicIndex(DatatableDTO $dto, FetchDestinationAgenciesList $action): JsonResponse
    {
        return PublicDestinationAgencyResource::collection(
            $action->handle($dto, onlyActive: true)
        )->response();
    }

    public function show(string $uuid, FetchDestinationAgency $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new DestinationAgencyResource($action->handle($uuid)));
    }

    public function store(CreateDestinationAgencyDTO $dto, CreateDestinationAgency $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new DestinationAgencyResource($action->handle($dto)),
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateDestinationAgencyDTO $dto, string $uuid, UpdateDestinationAgency $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new DestinationAgencyResource($action->handle($uuid, $dto)));
    }

    public function destroy(string $uuid, DeleteDestinationAgency $action): NoContentResponse
    {
        $action->handle($uuid);

        return new NoContentResponse();
    }
}
