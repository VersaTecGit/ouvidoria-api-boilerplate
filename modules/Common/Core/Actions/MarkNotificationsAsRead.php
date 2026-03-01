<?php

declare(strict_types=1);

namespace Modules\Common\Core\Actions;

final readonly class MarkNotificationsAsRead
{
    public function __construct(private FetchNotification $fetchNotification) {}

    public function handle(string $id): void
    {
        $notification = $this->fetchNotification->handle($id);
        $notification->markAsRead();
    }
}
