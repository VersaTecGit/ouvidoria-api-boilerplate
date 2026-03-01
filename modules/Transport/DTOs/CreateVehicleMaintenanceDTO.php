<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use Modules\Common\Core\DTOs\UploadedFileDTO;
use Modules\Transport\Support\VehicleMaintenanceService;
use Modules\Transport\Support\VehicleMaintenanceStatus;
use Modules\Transport\Support\VehicleMaintenanceType;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\FloatCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class CreateVehicleMaintenanceDTO extends ValidatedDTO
{
    public VehicleMaintenanceType $type;

    public VehicleMaintenanceService $service;

    public ?string $other_service;

    public ?CarbonImmutable $performed_at;

    public ?CarbonImmutable $scheduled_at;

    public ?string $workshop;

    public ?string $description;

    public ?float $cost;

    public VehicleMaintenanceStatus $status;

    public array $files;

    protected function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(VehicleMaintenanceType::toArray())],
            'service' => ['required', 'string', Rule::in(VehicleMaintenanceService::toArray())],
            'other_service' => ['sometimes', 'nullable', 'string', 'max:255'],
            'performed_at' => ['sometimes', 'nullable', 'date'],
            'scheduled_at' => ['sometimes', 'nullable', 'date'],
            'workshop' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', Rule::in(VehicleMaintenanceStatus::toArray())],
            'files' => ['sometimes', 'array'],
            'files.*.extension' => ['required', 'string', Rule::in(['jpeg', 'jpg', 'png', 'pdf'])],
        ];
    }

    protected function defaults(): array
    {
        return [
            'files' => [],
        ];
    }

    protected function casts(): array
    {
        return [
            'type' => new EnumCast(VehicleMaintenanceType::class),
            'service' => new EnumCast(VehicleMaintenanceService::class),
            'other_service' => new StringCast(),
            'performed_at' => new CarbonImmutableCast(),
            'scheduled_at' => new CarbonImmutableCast(),
            'workshop' => new StringCast(),
            'description' => new StringCast(),
            'cost' => new FloatCast(),
            'status' => new EnumCast(VehicleMaintenanceStatus::class),
            'files' => new ArrayCast(new DTOCast(UploadedFileDTO::class)),
        ];
    }
}
