<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Illuminate\Validation\Rule;
use Modules\Common\Core\Support\WeekDay;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\BooleanCast;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class DriverAvailabilityDTO extends ValidatedDTO
{
    public array $days;

    public string $start_time;

    public string $end_time;

    public bool $long_trips;

    protected function rules(): array
    {
        return [
            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['string', Rule::in(WeekDay::toArray())],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'long_trips' => ['required', 'boolean'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'days' => new ArrayCast(new EnumCast(WeekDay::class)),
            'start_time' => new StringCast(),
            'end_time' => new StringCast(),
            'long_trips' => new BooleanCast(),
        ];
    }
}
