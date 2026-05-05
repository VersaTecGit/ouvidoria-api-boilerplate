<?php

declare(strict_types=1);

namespace Modules\EmendasParlamentares\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\EmendasParlamentares\Actions\CreateConcedente;
use Modules\EmendasParlamentares\Actions\GetConcedente;
use Modules\EmendasParlamentares\DTOs\CreateConcedenteDTO;
use Modules\EmendasParlamentares\Resources\ConcedenteResource;

class ConcedenteController extends Controller
{
    public function store(CreateConcedente $action, Request $request): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new ConcedenteResource($action->handle(CreateConcedenteDTO::fromRequest($request))),
            Response::HTTP_CREATED
        );
    }

    public function index(Request $request, GetConcedente $action): JsonResponse
    {
        return $action->handle($request);
    }
}
