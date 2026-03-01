<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Modules\Auth\Models\User;
use WendellAdriel\ValidatedDTO\Casting\BooleanCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class VehicleTripUserPassengerDTO extends ValidatedDTO
{
    public int $id;

    public bool $was_present;

    public ?string $absence_reason;

    protected function rules(): array
    {
        return [
            'id' => ['required', 'string', 'max:255'],
            'was_present' => ['required', 'boolean'],
            'absence_reason' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'id' => fn (string $property, mixed $value) => User::findByUuid($value)->id,
            'was_present' => new BooleanCast(),
            'absence_reason' => new StringCast(),
        ];
    }
}
