<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Ouvidoria\Actions\CreateManifestation;
use Modules\Ouvidoria\Actions\FetchPublicManifestation;
use Modules\Ouvidoria\DTOs\CreatePublicManifestationDTO;
use Modules\Ouvidoria\Resources\PublicManifestationResource;

/**
 * The anonymous citizen surface: file a manifestation with no token, then
 * consult it by protocol. Both routes are throttled by IP and live outside
 * the `auth` group.
 *
 * Every response here goes through `PublicManifestationResource`. This
 * controller must never use `ManifestationResource` or any other staff
 * resource — the protocol is a bearer secret and whatever we return with it
 * is what leaks when it is shared.
 */
final class PublicManifestationController extends Controller
{
    public function store(CreatePublicManifestationDTO $dto, CreateManifestation $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new PublicManifestationResource($action->handle($dto)),
            Response::HTTP_CREATED
        );
    }

    public function show(string $protocol, FetchPublicManifestation $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new PublicManifestationResource($action->handle($protocol)));
    }
}
