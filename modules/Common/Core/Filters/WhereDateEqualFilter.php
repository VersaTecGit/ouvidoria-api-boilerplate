<?php

declare(strict_types=1);

namespace Modules\Common\Core\Filters;

use Illuminate\Database\Eloquent\Builder;
use Modules\Common\Core\Filters\Abstracts\Filter;

class WhereDateEqualFilter extends Filter
{
    public function apply(Builder $builder, mixed $value, string $filter): Builder
    {
        return $builder->whereDate($filter, $value);
    }
}
