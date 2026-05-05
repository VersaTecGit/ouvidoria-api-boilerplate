<?php

declare(strict_types=1);

namespace Modules\EmendasParlamentares\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\EmendasParlamentares\Actions\CreateRecebedor;
use Modules\EmendasParlamentares\Actions\GetRecebedor;
use Modules\EmendasParlamentares\DTOs\CreateRecebedorDTO;
use Modules\EmendasParlamentares\Resources\RecebedorResource;

class RecebedorController extends Controller
{
    public function store(CreateRecebedor $action, Request $request): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new RecebedorResource($action->handle(CreateRecebedorDTO::fromRequest($request))),
            Response::HTTP_CREATED
        );
    }

    public function index(Request $request, GetRecebedor $action): JsonResponse
    {
        return $action->handle($request);
    }
}
