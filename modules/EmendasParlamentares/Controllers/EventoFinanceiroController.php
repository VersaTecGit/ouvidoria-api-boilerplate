<?php

namespace Modules\EmendasParlamentares\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\EmendasParlamentares\Actions\CreateEventoFinanceiro;
use Modules\EmendasParlamentares\DTOs\CreateEventoFinanceiroDTO;
use Modules\EmendasParlamentares\Resources\EventoFinanceiroResource;

class EventoFinanceiroController extends Controller
{
    public function store(Request $request, CreateEventoFinanceiro $action) : ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new EventoFinanceiroResource($action->handle(CreateEventoFinanceiroDTO::fromRequest($request))),
            Response::HTTP_CREATED
        );
    }
}