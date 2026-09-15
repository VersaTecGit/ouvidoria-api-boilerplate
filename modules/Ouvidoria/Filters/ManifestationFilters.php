<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Filters;

use Modules\Common\Core\Filters\Abstracts\Filters;
use Modules\Common\Core\Filters\WhereDateEqualFilter;
use Modules\Common\Core\Filters\WhereDateGreaterOrEqualFilter;
use Modules\Common\Core\Filters\WhereDateLessOrEqualFilter;
use Modules\Common\Core\Filters\WhereFilter;

final class ManifestationFilters extends Filters
{
    protected array $filters = [
        'type' => WhereFilter::class,
        'status' => WhereFilter::class,
        'is_anonymous' => WhereFilter::class,
        'destination_agency_id' => WhereDestinationAgencyIdFilter::class,
        'unit_id' => WhereUnitIdFilter::class,
        'protocol_number' => WhereFilter::class,
        'created_at' => WhereDateEqualFilter::class,
        'begin' => WhereDateGreaterOrEqualFilter::class,
        'end' => WhereDateLessOrEqualFilter::class,
    ];
}
