<?php

declare(strict_types=1);

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Actions\FetchRolePermissionsList;
use Modules\Auth\Actions\SyncRolePermissions;
use Modules\Auth\DTOs\SyncRolePermissionsDTO;
use Modules\Auth\Resources\PermissionResource;
use Modules\Common\Core\Responses\NoContentResponse;

final class RolePermissionController extends Controller
{
    public function index(int $id, FetchRolePermissionsList $action): JsonResponse
    {
        return PermissionResource::collection($action->handle($id))->response();
    }

    public function store(SyncRolePermissionsDTO $dto, int $id, SyncRolePermissions $action): NoContentResponse
    {
        $action->handle($id, $dto);

        return new NoContentResponse();
    }
}
