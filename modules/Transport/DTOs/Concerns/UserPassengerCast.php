<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs\Concerns;

use Modules\Auth\Models\User;
use WendellAdriel\ValidatedDTO\Casting\Castable;

final class UserPassengerCast implements Castable
{
    public function cast(string $property, mixed $value): array
    {
        return collect($value)
            ->map(fn (string $uuid) => User::findByUuid($uuid)->id)
            ->toArray();
    }
}
