<?php

declare(strict_types=1);

namespace Modules\Transport\Filters;

use Modules\Common\Core\Filters\Abstracts\Filters;
use Modules\Common\Core\Filters\WhereDateEqualFilter;
use Modules\Common\Core\Filters\WhereDateGreaterFilter;
use Modules\Common\Core\Filters\WhereDateLessFilter;

final class VehicleRefuelFilters extends Filters
{
    protected array $filters = [
        'begin' => WhereDateGreaterFilter::class,
        'end' => WhereDateLessFilter::class,
        'created_at' => WhereDateEqualFilter::class,
    ];
}
