<?php

declare(strict_types=1);

namespace Modules\Common\Core\Actions;

use Illuminate\Notifications\DatabaseNotification;
use Modules\Auth\Actions\LoggedUser;

final readonly class FetchNotification
{
    public function __construct(
        private LoggedUser $loggedUser,
    ) {}

    public function handle(string $id): DatabaseNotification
    {
        $loggedUser = $this->loggedUser->handle();

        return $loggedUser->notifications()->where('id', $id)->firstOrFail();
    }
}
