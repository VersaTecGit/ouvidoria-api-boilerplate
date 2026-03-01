<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\Utils;
use Modules\Common\Core\DTOs\UploadedFileDTO;
use Modules\Transport\Support\LicenseCategory;
use Modules\Transport\Support\VehicleFuel;
use Modules\Transport\Support\VehicleStatus;
use Modules\Transport\Support\VehicleType;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class UpdateVehicleDTO extends ValidatedDTO
{
    use Utils;

    public ?array $required_license_categories;

    public ?string $plate;

    public ?string $model;

    public ?string $brand;

    public ?int $capacity;

    public ?string $color;

    public ?array $fuels;

    public ?string $manufacture_year;

    public ?string $renavam;

    public ?string $chassis_number;

    public ?VehicleType $type;

    public ?string $other_type;

    public ?VehicleStatus $status;

    public ?array $pictures_to_remove;

    public ?array $pictures;

    protected function rules(): array
    {
        return [
            'required_license_categories' => ['sometimes', 'array', 'min:1'],
            'required_license_categories.*' => ['required', 'string', Rule::in(LicenseCategory::toArray())],
            'plate' => ['sometimes', 'string', 'max:10', Rule::unique('vehicles', 'plate')],
            'model' => ['sometimes', 'string', 'max:255'],
            'brand' => ['sometimes', 'string', 'max:255'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'color' => ['sometimes', 'nullable', 'string', 'max:255'],
            'fuels' => ['sometimes', 'array', 'min:1'],
            'fuels.*' => ['required', 'string', Rule::in(VehicleFuel::toArray())],
            'manufacture_year' => ['sometimes', 'string'],
            'renavam' => ['sometimes', 'string', 'max:255', Rule::unique('vehicles', 'renavam')],
            'chassis_number' => ['sometimes', 'string', 'max:255', Rule::unique('vehicles', 'chassis_number')],
            'type' => ['sometimes', 'string', 'max:255', Rule::in(VehicleType::toArray())],
            'other_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'pictures_to_remove' => ['sometimes', 'array'],
            'pictures' => ['sometimes', 'array'],
            'pictures.*.extension' => ['required', 'string', Rule::in(['jpeg', 'jpg', 'png'])],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'required_license_categories' => new ArrayCast(new EnumCast(LicenseCategory::class)),
            'plate' => new StringCast(),
            'model' => new StringCast(),
            'brand' => new StringCast(),
            'capacity' => new IntegerCast(),
            'color' => new StringCast(),
            'fuels' => new ArrayCast(new EnumCast(VehicleFuel::class)),
            'manufacture_year' => new StringCast(),
            'renavam' => new StringCast(),
            'chassis_number' => new StringCast(),
            'type' => new EnumCast(VehicleType::class),
            'other_type' => new StringCast(),
            'status' => new EnumCast(VehicleStatus::class),
            'pictures' => new ArrayCast(new DTOCast(UploadedFileDTO::class)),
        ];
    }
}
