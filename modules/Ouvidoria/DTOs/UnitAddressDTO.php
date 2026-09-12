<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\DTOs;

use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class UnitAddressDTO extends ValidatedDTO
{
    public ?string $street;

    public ?string $number;

    public ?string $complement;

    public ?string $neighborhood;

    public ?string $city;

    public ?string $state;

    public ?string $postal_code;

    protected function rules(): array
    {
        return [
            'street' => ['sometimes', 'nullable', 'string', 'max:255'],
            'number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'complement' => ['sometimes', 'nullable', 'string', 'max:255'],
            'neighborhood' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:2'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:20'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'street' => new StringCast(),
            'number' => new StringCast(),
            'complement' => new StringCast(),
            'neighborhood' => new StringCast(),
            'city' => new StringCast(),
            'state' => new StringCast(),
            'postal_code' => new StringCast(),
        ];
    }
}
