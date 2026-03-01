<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use Modules\Common\Core\DTOs\Concerns\Utils;
use Modules\Transport\Support\VehicleFuel;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\FloatCast;
use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class UpdateVehicleRefuelDTO extends ValidatedDTO
{
    use Utils;

    public ?CarbonImmutable $refueled_at;

    public ?int $odometer;

    public ?float $liters;

    public ?float $price_per_liter;

    public ?float $total_value;

    public ?VehicleFuel $fuel_type;

    public ?VehicleRequestLocationDTO $station_location;

    public ?float $consumption;

    protected function rules(): array
    {
        return [
            'refueled_at' => ['sometimes', 'date'],
            'odometer' => ['sometimes', 'integer', 'min:0'],
            'liters' => ['sometimes', 'numeric', 'min:0'],
            'price_per_liter' => ['sometimes', 'numeric', 'min:0'],
            'total_value' => ['sometimes', 'numeric', 'min:0'],
            'fuel_type' => ['sometimes', Rule::in(VehicleFuel::toArray())],
            'station_location' => ['sometimes', 'nullable', 'array'],
            'consumption' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'refueled_at' => new CarbonImmutableCast(),
            'odometer' => new IntegerCast(),
            'liters' => new FloatCast(),
            'price_per_liter' => new FloatCast(),
            'total_value' => new FloatCast(),
            'fuel_type' => new EnumCast(VehicleFuel::class),
            'station_location' => new DTOCast(VehicleRequestLocationDTO::class),
            'consumption' => new FloatCast(),
        ];
    }
}
