<?php

declare(strict_types=1);

namespace Modules\Common\Core\Actions;

use Modules\Auth\Actions\LoggedUser;

final readonly class CountUnreadNotifications
{
    public function __construct(
        private LoggedUser $loggedUser,
    ) {}

    public function handle(): array
    {
        $loggedUser = $this->loggedUser->handle();

        return [
            'unread_count' => $loggedUser->unreadNotifications()->count(),
        ];
    }
}
