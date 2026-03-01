<?php

declare(strict_types=1);

namespace Modules\Common\Core\Support;

use Illuminate\Database\Eloquent\Builder;
use Modules\Common\Core\DTOs\DateRangeDTO;

readonly class DateRangeDatatable extends Datatable
{
    public static function applyDateRangeFilter(Builder $builder, DateRangeDTO $dto, string $dateField = 'created_at', bool $hasJoinedFields = false): Builder
    {
        if (! (isset($dto->start_date, $dto->end_date))) {
            return $builder;
        }

        $field = $hasJoinedFields ? "{$builder->getModel()->getTable()}.{$dateField}" : $dateField;
        if (! empty($dto->start_date)) {
            $builder->where($field, '>=', $dto->start_date->startOfDay());
        }

        if (! empty($dto->end_date)) {
            $builder->where($field, '<=', $dto->end_date->endOfDay());
        }

        return $builder;
    }
}
