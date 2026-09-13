<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\DTOs;

use WendellAdriel\ValidatedDTO\Casting\BooleanCast;
use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class CreateDestinationAgencyDTO extends ValidatedDTO
{
    public string $name;

    public bool $active;

    public int $order;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    protected function defaults(): array
    {
        return [
            'active' => true,
            'order' => 0,
        ];
    }

    protected function casts(): array
    {
        return [
            'name' => new StringCast(),
            'active' => new BooleanCast(),
            'order' => new IntegerCast(),
        ];
    }
}
