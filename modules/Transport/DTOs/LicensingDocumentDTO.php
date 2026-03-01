<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use Modules\Transport\Support\VehicleLicensingStatus;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class LicensingDocumentDTO extends ValidatedDTO
{
    public int $year;

    public int $fee;

    public CarbonImmutable $due_date;

    public VehicleLicensingStatus $status;

    protected function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'min:1900', 'max:2999'],
            'fee' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'status' => ['required', Rule::in(VehicleLicensingStatus::toArray())],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'year' => new IntegerCast(),
            'fee' => new IntegerCast(),
            'due_date' => new CarbonImmutableCast(),
            'status' => new EnumCast(VehicleLicensingStatus::class),
        ];
    }
}
