<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Controllers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Questionnaires\Actions\CreatePublicQuestionnaireSignedStorageUrl;
use Modules\Questionnaires\DTOs\CreatePublicQuestionnaireSignedStorageUrlDTO;

final class PublicQuestionnaireSignedStorageUrlController extends Controller
{
    public function store(CreatePublicQuestionnaireSignedStorageUrlDTO $dto, string $uuid, CreatePublicQuestionnaireSignedStorageUrl $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new JsonResource($action->handle($uuid, $dto)),
            Response::HTTP_CREATED
        );
    }
}
