<?php

declare(strict_types=1);

namespace Modules\Transport\Filters;

use Illuminate\Database\Eloquent\Builder;
use Modules\Common\Core\Filters\Abstracts\Filter;

class WhereRequesterIdFilter extends Filter
{
    public function apply(Builder $builder, mixed $value, string $filter): Builder
    {
        return $builder->whereHas('requester', function ($query) use ($value) {
            $query->where('uuid', $value);
        });
    }
}
