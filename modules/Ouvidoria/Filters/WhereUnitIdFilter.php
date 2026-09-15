<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Filters;

use Illuminate\Database\Eloquent\Builder;
use Modules\Common\Core\Filters\Abstracts\Filter;

class WhereUnitIdFilter extends Filter
{
    public function apply(Builder $builder, mixed $value, string $filter): Builder
    {
        return $builder->whereHas('unit', function ($query) use ($value) {
            $query->where('uuid', $value);
        });
    }
}
