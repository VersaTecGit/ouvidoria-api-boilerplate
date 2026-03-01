<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use Modules\Transport\Support\VehicleTripOccurrenceType;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class VehicleTripOccurrenceDTO extends ValidatedDTO
{
    public VehicleTripOccurrenceType $type;

    public ?string $description;

    public ?CarbonImmutable $occurred_at;

    public ?VehicleRequestLocationDTO $location;

    protected function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(VehicleTripOccurrenceType::toArray())],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'occurred_at' => ['sometimes', 'nullable', 'date'],
            'location' => ['sometimes', 'nullable', 'array'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'type' => new EnumCast(VehicleTripOccurrenceType::class),
            'description' => new StringCast(),
            'occurred_at' => new CarbonImmutableCast(),
            'location' => new DTOCast(VehicleRequestLocationDTO::class),
        ];
    }
}
