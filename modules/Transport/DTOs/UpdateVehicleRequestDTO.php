<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\Utils;
use Modules\Transport\DTOs\Concerns\UserPassengerCast;
use Modules\Transport\Support\VehicleRequestPriority;
use Modules\Transport\Support\VehicleRequestStatus;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class UpdateVehicleRequestDTO extends ValidatedDTO
{
    use Utils;

    public ?VehicleRequestStatus $status;

    public ?string $vehicle_id;

    public ?string $driver_id;

    public ?string $response;

    public ?VehicleRequestPriority $priority;

    public ?array $user_passengers;

    public ?array $travel_allowances;

    protected function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(VehicleRequestStatus::toArray())],
            'vehicle_id' => ['sometimes', 'uuid', Rule::exists('vehicles', 'uuid')],
            'driver_id' => ['sometimes', 'uuid', Rule::exists('users', 'uuid')],
            'response' => ['sometimes', 'nullable', 'string'],
            'priority' => ['sometimes', 'string', Rule::in(VehicleRequestPriority::toArray())],
            'user_passengers' => ['sometimes', 'array'],
            'user_passengers.*' => ['uuid', Rule::exists('users', 'uuid')],
            'approver_notes' => ['sometimes', 'nullable', 'string'],
            'travel_allowances' => ['sometimes', 'array'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'status' => new EnumCast(VehicleRequestStatus::class),
            'vehicle_id' => new StringCast(),
            'driver_id' => new StringCast(),
            'response' => new StringCast(),
            'priority' => new EnumCast(VehicleRequestPriority::class),
            'user_passengers' => new UserPassengerCast(),
            'approver_notes' => new StringCast(),
            'travel_allowances' => new ArrayCast(new DTOCast(TravelAllowanceDTO::class)),
        ];
    }
}
