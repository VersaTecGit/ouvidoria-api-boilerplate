<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\DTOs;

use Modules\Common\Core\DTOs\Concerns\Utils;
use WendellAdriel\ValidatedDTO\Casting\BooleanCast;
use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class UpdateDestinationAgencyDTO extends ValidatedDTO
{
    use Utils;

    public ?string $name;

    public ?bool $active;

    public ?int $order;

    protected function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    protected function defaults(): array
    {
        return [];
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
