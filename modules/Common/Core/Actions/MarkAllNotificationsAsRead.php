<?php

declare(strict_types=1);

namespace Modules\Common\Core\Actions;

use Modules\Auth\Actions\LoggedUser;

final readonly class MarkAllNotificationsAsRead
{
    public function __construct(
        private LoggedUser $loggedUser,
    ) {}

    public function handle(): void
    {
        $loggedUser = $this->loggedUser->handle();

        $loggedUser->unreadNotifications()->update(['read_at' => now()]);
    }
}
