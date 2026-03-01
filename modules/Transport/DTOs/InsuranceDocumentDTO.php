<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use Modules\Transport\Support\VehicleInsuranceStatus;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class InsuranceDocumentDTO extends ValidatedDTO
{
    public string $policy_number;

    public string $insurer;

    public int $premium;

    public CarbonImmutable $start_date;

    public CarbonImmutable $end_date;

    public VehicleInsuranceStatus $status;

    protected function rules(): array
    {
        return [
            'policy_number' => ['required', 'string', 'max:100'],
            'insurer' => ['required', 'string', 'max:255'],
            'premium' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['required', Rule::in(VehicleInsuranceStatus::toArray())],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'policy_number' => new StringCast(),
            'insurer' => new StringCast(),
            'premium' => new IntegerCast(),
            'start_date' => new CarbonImmutableCast(),
            'end_date' => new CarbonImmutableCast(),
            'status' => new EnumCast(VehicleInsuranceStatus::class),
        ];
    }
}
