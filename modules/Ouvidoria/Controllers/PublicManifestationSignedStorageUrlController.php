<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Ouvidoria\Actions\CreatePublicManifestationSignedStorageUrl;
use Modules\Ouvidoria\DTOs\CreatePublicManifestationSignedStorageUrlDTO;

final class PublicManifestationSignedStorageUrlController extends Controller
{
    public function store(
        CreatePublicManifestationSignedStorageUrlDTO $dto,
        CreatePublicManifestationSignedStorageUrl $action,
    ): ApiSuccessResponse {
        return new ApiSuccessResponse(
            new JsonResource($action->handle($dto)),
            Response::HTTP_CREATED
        );
    }
}
