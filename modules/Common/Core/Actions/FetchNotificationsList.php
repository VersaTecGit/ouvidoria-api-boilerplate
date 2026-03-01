<?php

declare(strict_types=1);

namespace Modules\Common\Core\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Auth\Actions\LoggedUser;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;

final readonly class FetchNotificationsList
{
    public function __construct(
        private LoggedUser $loggedUser,
    ) {}

    public function handle(DatatableDTO $dto): LengthAwarePaginator|Collection
    {
        $loggedUser = $this->loggedUser->handle();

        $query = DatabaseNotification::query()
            ->where('notifiable_id', $loggedUser->id)
            ->where('notifiable_type', get_class($loggedUser));
        $query = Datatable::applySort($query, $dto);

        return Datatable::applyPagination($query, $dto);
    }
}
