<?php

declare(strict_types=1);

namespace Modules\Auth\DTOs;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

final class CreateRoleDTO extends ValidatedDTO
{
    public string $name;

    public ?string $description;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:4', 'unique:roles,name', 'max:255'],
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
