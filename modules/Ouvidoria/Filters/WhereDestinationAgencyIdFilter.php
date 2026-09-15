<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Filters;

use Illuminate\Database\Eloquent\Builder;
use Modules\Common\Core\Filters\Abstracts\Filter;

/**
 * The API speaks UUIDs; the column holds the internal id, so the filter
 * resolves through the relation instead of comparing the raw value.
 */
class WhereDestinationAgencyIdFilter extends Filter
{
    public function apply(Builder $builder, mixed $value, string $filter): Builder
    {
        return $builder->whereHas('destinationAgency', function ($query) use ($value) {
            $query->where('uuid', $value);
        });
    }
}
