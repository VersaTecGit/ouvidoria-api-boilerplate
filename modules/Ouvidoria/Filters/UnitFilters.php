<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Filters;

use Modules\Common\Core\Filters\Abstracts\Filters;
use Modules\Common\Core\Filters\WhereDateEqualFilter;
use Modules\Common\Core\Filters\WhereFilter;

final class UnitFilters extends Filters
{
    protected array $filters = [
        'active' => WhereFilter::class,
        'unit_type_id' => WhereFilter::class,
        'created_at' => WhereDateEqualFilter::class,
    ];
}
