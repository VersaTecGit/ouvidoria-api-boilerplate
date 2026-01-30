<?php

namespace Modules\EmendasParlamentares\Controllers;

use App\Http\Controllers\Controller;
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

    public function index (GetRecebedor $action) {
        $lista = $action->handle();
        return response()->json(["status" => "success", "data" => $lista]);
    }
}