<?php

declare(strict_types=1);

namespace Modules\Common\Core\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Common\Core\Actions\CountUnreadNotifications;
use Modules\Common\Core\Actions\DeleteAllNotifications;
use Modules\Common\Core\Actions\DeleteNotification;
use Modules\Common\Core\Actions\DeleteReadNotifications;
use Modules\Common\Core\Actions\FetchNotification;
use Modules\Common\Core\Actions\FetchNotificationsList;
use Modules\Common\Core\Actions\MarkAllNotificationsAsRead;
use Modules\Common\Core\Actions\MarkNotificationsAsRead;
use Modules\Common\Core\Actions\MarkNotificationsAsUnread;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Resources\CountUnreadNotificationsResource;
use Modules\Common\Core\Resources\NotificationResource;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;

final class NotificationController extends Controller
{
    public function index(DatatableDTO $dto, FetchNotificationsList $action): JsonResponse
    {
        return NotificationResource::collection($action->handle($dto))->response();
    }

    public function unreadCount(CountUnreadNotifications $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new CountUnreadNotificationsResource($action->handle()));
    }

    public function readAll(MarkAllNotificationsAsRead $action): NoContentResponse
    {
        $action->handle();

        return new NoContentResponse();
    }

    public function destroyAll(DeleteAllNotifications $action): NoContentResponse
    {
        $action->handle();

        return new NoContentResponse();
    }

    public function destroyRead(DeleteReadNotifications $action): NoContentResponse
    {
        $action->handle();

        return new NoContentResponse();
    }

    public function show(string $id, FetchNotification $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new NotificationResource($action->handle($id)));
    }

    public function markAsRead(string $id, MarkNotificationsAsRead $action): NoContentResponse
    {
        $action->handle($id);

        return new NoContentResponse();
    }

    public function markAsUnread(string $id, MarkNotificationsAsUnread $action): NoContentResponse
    {
        $action->handle($id);

        return new NoContentResponse();
    }

    public function destroy(string $id, DeleteNotification $action): NoContentResponse
    {
        $action->handle($id);

        return new NoContentResponse();
    }
}
