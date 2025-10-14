<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Modules\Auth\Support\Permissions;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;
use Modules\Questionnaires\Actions\CreateQuestionnaire;
use Modules\Questionnaires\Actions\DeleteQuestionnaire;
use Modules\Questionnaires\Actions\FetchQuestionnaire;
use Modules\Questionnaires\Actions\FetchQuestionnairesList;
use Modules\Questionnaires\Actions\UpdateQuestionnaire;
use Modules\Questionnaires\DTOs\CreateQuestionnaireDTO;
use Modules\Questionnaires\DTOs\QuestionnaireVersionDTO;
use Modules\Questionnaires\DTOs\UpdateQuestionnaireDTO;
use Modules\Questionnaires\Resources\QuestionnaireResource;

final class QuestionnaireController extends Controller
{
    public function index(DatatableDTO $dto, FetchQuestionnairesList $action): JsonResponse
    {
        return QuestionnaireResource::collection($action->handle($dto))->response();
    }

    public function store(CreateQuestionnaireDTO $dto, CreateQuestionnaire $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new QuestionnaireResource($action->handle($dto)),
            Response::HTTP_CREATED
        );
    }

    public function show(QuestionnaireVersionDTO $dto, string $uuid, FetchQuestionnaire $action): ApiSuccessResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $questionnaire = $action->handle($uuid, $dto);

        if (! $questionnaire->active && (! $user || ! $user->can(Permissions::VIEW_QUESTIONNAIRES->value))) {
            throw new UnauthorizedException('Você não tem permissão para visualizar este questionário');
        }

        return new ApiSuccessResponse(new QuestionnaireResource($questionnaire));
    }

    public function update(UpdateQuestionnaireDTO $dto, string $uuid, UpdateQuestionnaire $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new QuestionnaireResource($action->handle($uuid, $dto)));
    }

    public function destroy(string $uuid, DeleteQuestionnaire $action): NoContentResponse
    {
        $action->handle($uuid);

        return new NoContentResponse();
    }
}
