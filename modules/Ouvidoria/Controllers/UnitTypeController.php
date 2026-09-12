<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Ouvidoria\Actions\FetchUnitTypesList;
use Modules\Ouvidoria\Resources\UnitTypeResource;

final class UnitTypeController extends Controller
{
    public function index(DatatableDTO $dto, FetchUnitTypesList $action): JsonResponse
    {
        return UnitTypeResource::collection($action->handle($dto))->response();
    }
}
