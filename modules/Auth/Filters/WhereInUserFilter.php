<?php

declare(strict_types=1);

namespace Modules\Auth\Filters;

use Illuminate\Database\Eloquent\Builder;
use Modules\Common\Core\Filters\Abstracts\Filter;

class WhereInUserFilter extends Filter
{
    public function apply(Builder $builder, mixed $value, string $filter): Builder
    {
        $users = request()->string('users')->explode(',');

        $builder->whereHas('users', function (Builder $query) use ($users) {
            $query->whereIn('uuid', $users);
        });

        return $builder;
    }
}
