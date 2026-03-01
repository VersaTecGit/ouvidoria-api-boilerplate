<?php

declare(strict_types=1);

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Actions\FetchRoleMember;
use Modules\Auth\Resources\RoleMemberResource;
use Modules\Common\Core\DTOs\DatatableDTO;

final class RoleMemberController extends Controller
{
    public function index(int $id, DatatableDTO $dto, FetchRoleMember $action): JsonResponse
    {
        return RoleMemberResource::collection($action->handle($id, $dto))->response();
    }
}
