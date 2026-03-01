<?php

declare(strict_types=1);

namespace Modules\Auth\DTOs;

use Modules\Common\Core\DTOs\Concerns\Utils;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

final class UpdateRoleDTO extends ValidatedDTO
{
    use Utils;

    public ?string $name;

    public ?string $description;

    protected function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:4', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [];
    }
}
