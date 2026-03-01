<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Illuminate\Validation\Rule;
use WendellAdriel\ValidatedDTO\Casting\FloatCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

final class TravelAllowanceDTO extends ValidatedDTO
{
    public string $beneficiary_type;

    public string $beneficiary_id;

    public string $purpose;

    public float $days_requested;

    public float $unit_value;

    protected function rules(): array
    {
        return [
            'beneficiary_type' => ['required', 'string', Rule::in(['user'])],
            'beneficiary_id' => ['required', 'uuid'],
            'purpose' => ['required', 'string'],
            'days_requested' => ['required', 'numeric', 'min:0.5'],
            'unit_value' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'beneficiary_type' => new StringCast(),
            'beneficiary_id' => new StringCast(),
            'purpose' => new StringCast(),
            'days_requested' => new FloatCast(),
            'unit_value' => new FloatCast(),
        ];
    }
}
