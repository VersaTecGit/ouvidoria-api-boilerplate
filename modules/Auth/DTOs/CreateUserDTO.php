<?php

declare(strict_types=1);

namespace Modules\Auth\DTOs;

use Illuminate\Validation\Rules\Password;
use Modules\Auth\Models\Role;
use Modules\Transport\DTOs\DriverDTO;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

final class CreateUserDTO extends ValidatedDTO
{
    public string $name;

    public string $login;

    public string $email;

    public string $password;

    public Role $role;

    public array $extra_permissions;

    public ?DriverDTO $driver;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:4', 'max:255'],
            'login' => ['required', 'string', 'min:4', 'max:20', 'alpha_dash', 'unique:users,login'],
            'email' => ['required', 'email', 'unique:users,email', 'max:255'],
            'password' => [
                'required',
                Password::min(8)->max(255),
                'confirmed',
            ],
            'role' => ['required', 'int', 'exists:roles,id', 'not_in:1'],
            'extra_permissions' => ['sometimes', 'array'],
            'extra_permissions.*' => ['exists:permissions,name'],
            'driver' => ['sometimes', 'nullable', 'array'],
        ];
    }

    protected function defaults(): array
    {
        return [
            'extra_permissions' => [],
        ];
    }

    protected function casts(): array
    {
        return [
            'name' => new StringCast(),
            'login' => new StringCast(),
            'email' => new StringCast(),
            'password' => new StringCast(),
            'role' => fn (string $property, mixed $value) => Role::findById($value),
            'extra_permissions' => new ArrayCast(new StringCast()),
            'driver' => new DTOCast(DriverDTO::class),
        ];
    }
}
