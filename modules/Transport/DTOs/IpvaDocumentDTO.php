<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use Modules\Transport\Support\VehicleIpvaStatus;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class IpvaDocumentDTO extends ValidatedDTO
{
    public int $year;

    public int $amount;

    public CarbonImmutable $due_date;

    public VehicleIpvaStatus $status;

    protected function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'min:1900', 'max:2999'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'status' => ['required', Rule::in(VehicleIpvaStatus::toArray())],
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
            'amount' => new IntegerCast(),
            'due_date' => new CarbonImmutableCast(),
            'status' => new EnumCast(VehicleIpvaStatus::class),
        ];
    }
}
