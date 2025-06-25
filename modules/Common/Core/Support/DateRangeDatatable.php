<?php

declare(strict_types=1);

namespace Modules\Common\Core\Support;

use Illuminate\Database\Eloquent\Builder;
use Modules\Common\Core\DTOs\DateRangeDTO;

readonly class DateRangeDatatable extends Datatable
{
    public static function applyDateRangeFilter(Builder $builder, DateRangeDTO $dto, string $dateField = 'created_at'): Builder
    {
        if (empty($dto->start_date) && empty($dto->end_date)) {
            return $builder;
        }

        if (! empty($dto->start_date)) {
            $builder->where($dateField, '>=', $dto->start_date->startOfDay());
        }

        if (! empty($dto->end_date)) {
            $builder->where($dateField, '<=', $dto->end_date->endOfDay());
        }

        return $builder;
    }
}
