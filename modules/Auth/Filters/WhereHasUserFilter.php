<?php

declare(strict_types=1);

namespace Modules\Auth\Filters;

use Illuminate\Database\Eloquent\Builder;
use Modules\Common\Core\Filters\Abstracts\Filter;

class WhereHasUserFilter extends Filter
{
    public function apply(Builder $builder, mixed $value, string $filter): Builder
    {
        return $builder->whereHas('users', function ($query) use ($value) {
            $query->where('uuid', $value);
        });
    }
}
