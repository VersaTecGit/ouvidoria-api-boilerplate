<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Auth\Filters\UserFilters;
use Modules\Auth\Models\User;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;
use Modules\Common\Core\Support\Modules;
use Modules\Common\Logs\Support\AccessActions;
use Modules\Common\Logs\Support\AccessLogHelper;

final readonly class FetchUsersList
{
    public function __construct(private UserFilters $filters) {}

    public function handle(DatatableDTO $dto): LengthAwarePaginator|Collection
    {
        $query = User::query()->with([
            'roles',
            'latestLogin',
        ])->filtered($this->filters)->all();
        $query = Datatable::applyFilter($query, $dto, ['login', 'email', 'name']);
        $query = Datatable::applySort($query, $dto);

        if ($dto->log) {
            AccessLogHelper::log(
                action: AccessActions::DATATABLE_VIEW,
                module: Modules::USERS,
                customMessage: AccessActions::DATATABLE_VIEW->message() . Modules::USERS->description()
            );
        }

        return Datatable::applyPagination($query, $dto);
    }
}
