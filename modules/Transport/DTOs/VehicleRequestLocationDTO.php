<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use WendellAdriel\ValidatedDTO\Casting\FloatCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class VehicleRequestLocationDTO extends ValidatedDTO
{
    public ?string $label;

    public string $address;

    public float $lat;

    public float $lng;

    protected function rules(): array
    {
        return [
            'label' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'label' => new StringCast(),
            'address' => new StringCast(),
            'lat' => new FloatCast(),
            'lng' => new FloatCast(),
        ];
    }
}
