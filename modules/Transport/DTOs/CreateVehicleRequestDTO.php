<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Modules\Auth\Models\User;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use Modules\Common\Core\DTOs\UploadedFileDTO;
use Modules\Transport\DTOs\Concerns\UserPassengerCast;
use Modules\Transport\Support\VehicleRequestPriority;
use Modules\Transport\Support\VehicleRequestStatus;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class CreateVehicleRequestDTO extends ValidatedDTO
{
    public int $requester_id;

    public VehicleRequestLocationDTO $origin;

    public VehicleRequestLocationDTO $destination;

    public ?array $waypoints;

    public CarbonImmutable $departure_at;

    public ?CarbonImmutable $return_at;

    public VehicleRequestPriority $priority;

    public string $justification;

    public VehicleRequestStatus $status;

    public array $attachments;

    public array $user_passengers;

    protected function rules(): array
    {
        return [
            'requester_id' => ['required', 'uuid', Rule::exists('users', 'uuid')],
            'origin' => ['required', 'array'],
            'destination' => ['required', 'array'],
            'waypoints' => ['sometimes', 'nullable', 'array'],
            'departure_at' => ['required', 'date'],
            'return_at' => ['sometimes', 'nullable', 'date'],
            'priority' => ['required', 'string', Rule::in(VehicleRequestPriority::toArray())],
            'justification' => ['required', 'string'],
            'attachments' => ['sometimes', 'array', 'max:5'],
            'user_passengers' => ['sometimes', 'array'],
            'user_passengers.*' => ['uuid', Rule::exists('users', 'uuid')],
        ];
    }

    protected function defaults(): array
    {
        return [
            'status' => VehicleRequestStatus::PENDING,
            'attachments' => [],
            'user_passengers' => [],
        ];
    }

    protected function casts(): array
    {
        return [
            'requester_id' => fn (string $property, mixed $value) => User::findByUuid($value)->id,
            'origin' => new DTOCast(VehicleRequestLocationDTO::class),
            'destination' => new DTOCast(VehicleRequestLocationDTO::class),
            'waypoints' => new ArrayCast(new DTOCast(VehicleRequestLocationDTO::class)),
            'departure_at' => new CarbonImmutableCast(),
            'return_at' => new CarbonImmutableCast(),
            'priority' => new EnumCast(VehicleRequestPriority::class),
            'justification' => new StringCast(),
            'status' => new EnumCast(VehicleRequestStatus::class),
            'attachments' => new ArrayCast(new DTOCast(UploadedFileDTO::class)),
            'user_passengers' => new UserPassengerCast(),
        ];
    }
}
