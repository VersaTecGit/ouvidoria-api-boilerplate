<?php

declare(strict_types=1);

namespace Modules\Transport\Filters;

use Modules\Common\Core\Filters\Abstracts\Filters;
use Modules\Common\Core\Filters\WhereDateEqualFilter;
use Modules\Common\Core\Filters\WhereDateGreaterFilter;
use Modules\Common\Core\Filters\WhereDateLessFilter;
use Modules\Common\Core\Filters\WhereFilter;

final class VehicleRequestFilters extends Filters
{
    protected array $filters = [
        'requester_id' => WhereRequesterIdFilter::class,
        'driver_id' => WhereDriverIdFilter::class,
        'status' => WhereFilter::class,
        'priority' => WhereFilter::class,
        'begin' => WhereDateGreaterFilter::class,
        'end' => WhereDateLessFilter::class,
        'created_at' => WhereDateEqualFilter::class,
        'departure_is_same_day' => WhereDepartureIsSameDayFilter::class,
        'departure_greater' => WhereDepartureGreaterFilter::class,
        'return_less' => WhereReturnLessFilter::class,
    ];
}
