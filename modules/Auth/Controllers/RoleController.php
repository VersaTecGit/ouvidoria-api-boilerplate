<?php

declare(strict_types=1);

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Auth\Actions\CreateRole;
use Modules\Auth\Actions\DeleteRole;
use Modules\Auth\Actions\FetchRole;
use Modules\Auth\Actions\FetchRolesList;
use Modules\Auth\Actions\UpdateRole;
use Modules\Auth\DTOs\CreateRoleDTO;
use Modules\Auth\DTOs\UpdateRoleDTO;
use Modules\Auth\Resources\RoleResource;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;

final class RoleController extends Controller
{
    public function index(DatatableDTO $dto, FetchRolesList $action): JsonResponse
    {
        return RoleResource::collection($action->handle($dto))->response();
    }

    public function show(int $id, FetchRole $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new RoleResource($action->handle($id)));
    }

    public function store(CreateRoleDTO $dto, CreateRole $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new RoleResource($action->handle($dto)),
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateRoleDTO $dto, int $id, UpdateRole $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new RoleResource($action->handle($id, $dto)));
    }

    public function destroy(int $id, DeleteRole $action): NoContentResponse
    {
        $action->handle($id);

        return new NoContentResponse();
    }
}
