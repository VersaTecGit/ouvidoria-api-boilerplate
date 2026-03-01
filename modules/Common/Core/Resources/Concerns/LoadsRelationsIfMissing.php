<?php

declare(strict_types=1);

namespace Modules\Common\Core\Resources\Concerns;

trait LoadsRelationsIfMissing
{
    protected function loadIfMissing(string $relation): mixed
    {
        return $this->relationLoaded($relation)
            ? $this->{$relation}
            : $this->load($relation)->{$relation};
    }
}
