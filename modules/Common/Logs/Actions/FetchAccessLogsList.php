<?php

declare(strict_types=1);

namespace Modules\Common\Logs\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Common\Core\DTOs\DateRangeDTO;
use Modules\Common\Core\Support\DateRangeDatatable;
use Modules\Common\Core\Support\Modules;
use Modules\Common\Logs\Filters\AccessLogFilters;
use Modules\Common\Logs\Models\AccessLog;
use Modules\Common\Logs\Support\AccessActions;
use Modules\Common\Logs\Support\AccessLogHelper;

final readonly class FetchAccessLogsList
{
    public function __construct(private AccessLogFilters $filters) {}

    public function handle(DateRangeDTO $dto): LengthAwarePaginator|Collection
    {
        $query = AccessLog::query()->filtered($this->filters)->all();
        $query = DateRangeDatatable::applyFilter($query, $dto, ['message', 'action', 'user_name']);
        $query = DateRangeDatatable::applySort($query, $dto);
        $query = DateRangeDatatable::applyDateRangeFilter($query, $dto, 'access_logs.created_at');

        if ($dto->log) {
            AccessLogHelper::log(
                action: AccessActions::DATATABLE_VIEW,
                module: Modules::ACCESS_LOGS,
                customMessage: AccessActions::DATATABLE_VIEW->message() . Modules::ACCESS_LOGS->description()
            );
        }

        return DateRangeDatatable::applyPagination($query, $dto);
    }
}
