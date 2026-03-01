<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use Modules\Transport\Support\DriverCnhStatus;
use Modules\Transport\Support\LicenseCategory;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class DriverDTO extends ValidatedDTO
{
    public string $cnh_number;

    public array $cnh_categories;

    public CarbonImmutable $cnh_emission_date;

    public CarbonImmutable $cnh_expiration_date;

    public DriverCnhStatus $cnh_status;

    public DriverAvailabilityDTO $availability;

    protected function rules(): array
    {
        return [
            'cnh_number' => ['required', 'string', 'max:20'],
            'cnh_categories' => ['required', 'array', 'min:1'],
            'cnh_categories.*' => ['string', Rule::in(LicenseCategory::toArray())],
            'cnh_emission_date' => ['required', 'date'],
            'cnh_expiration_date' => ['required', 'date'],
            'cnh_status' => ['required', Rule::in(DriverCnhStatus::toArray())],
            'availability' => ['required', 'array'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'cnh_number' => new StringCast(),
            'cnh_categories' => new ArrayCast(new EnumCast(LicenseCategory::class)),
            'cnh_emission_date' => new CarbonImmutableCast(),
            'cnh_expiration_date' => new CarbonImmutableCast(),
            'cnh_status' => new EnumCast(DriverCnhStatus::class),
            'availability' => new DTOCast(DriverAvailabilityDTO::class),
        ];
    }
}
