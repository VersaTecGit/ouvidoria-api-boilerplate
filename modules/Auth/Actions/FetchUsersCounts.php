<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Illuminate\Support\Collection;
use Modules\Auth\Models\User;

final readonly class FetchUsersCounts
{
    public function handle(): Collection
    {
        $grouped = User::query()
            ->all()
            ->get()
            ->groupBy(fn (User $user) => $user->active ? 'active' : 'inactive')
            ->mapWithKeys(fn (Collection $group, string $key) => [$key => $group->count()]);

        $grouped->when(
            ! $grouped->has('active'),
            fn (Collection $group) => $group->put('active', 0)
        );

        $grouped->when(
            ! $grouped->has('inactive'),
            fn (Collection $group) => $group->put('inactive', 0)
        );

        $grouped->put('total', $grouped->sum());

        return $grouped;
    }
}
