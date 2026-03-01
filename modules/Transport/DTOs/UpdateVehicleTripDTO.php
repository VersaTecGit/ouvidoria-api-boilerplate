<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use Modules\Common\Core\DTOs\Concerns\Utils;
use Modules\Transport\Support\VehicleTripStatus;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class UpdateVehicleTripDTO extends ValidatedDTO
{
    use Utils;

    public ?VehicleTripStatus $status;

    public ?CarbonImmutable $started_at;

    public ?CarbonImmutable $finished_at;

    public ?array $user_passengers;

    public ?array $occurrences;

    protected function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(VehicleTripStatus::toArray())],
            'started_at' => ['sometimes', 'date'],
            'finished_at' => ['sometimes', 'date'],
            'user_passengers' => ['sometimes', 'array'],
            'occurrences' => ['sometimes', 'array'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'status' => new EnumCast(VehicleTripStatus::class),
            'started_at' => new CarbonImmutableCast(),
            'finished_at' => new CarbonImmutableCast(),
            'user_passengers' => new ArrayCast(new DTOCast(VehicleTripUserPassengerDTO::class)),
            'occurrences' => new ArrayCast(new DTOCast(VehicleTripOccurrenceDTO::class)),
        ];
    }
}
