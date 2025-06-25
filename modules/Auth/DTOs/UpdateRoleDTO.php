<?php

declare(strict_types=1);

namespace Modules\Auth\DTOs;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

final class UpdateRoleDTO extends ValidatedDTO
{
    public ?string $name;

    public ?string $description;

    protected function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:4', 'max:255'],
            'description' => ['sometimes', 'string', 'max:255'],
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
