<?php

namespace Modules\EmendasParlamentares\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\EmendasParlamentares\Actions\CreateEmenda;
use Modules\EmendasParlamentares\Actions\FetchEmendasList;
use Modules\EmendasParlamentares\Actions\UpdateEmenda;
use Modules\EmendasParlamentares\DTOs\CreateEmendaDTO;
use Modules\EmendasParlamentares\DTOs\UpdateEmendaDTO;
use Modules\EmendasParlamentares\Resources\EmendaResourse;

class EmendaController extends Controller
{
    public function store(CreateEmenda $action, Request $request)
    {
        return new ApiSuccessResponse(
            new EmendaResourse($action->handle(CreateEmendaDTO::fromRequest($request))),
            Response::HTTP_CREATED
        );
    }

    public function index(DatatableDTO $dto, FetchEmendasList $action)
    {
        return EmendaResourse::collection($action->handle($dto))->response();
    }

    public function update(int $id, UpdateEmenda $action, Request $request)
    {
        return new ApiSuccessResponse(
            new EmendaResourse($action->handle($id, UpdateEmendaDTO::fromRequest($request))),
            Response::HTTP_OK
        );
    }
}