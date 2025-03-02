<?php

declare(strict_types=1);

namespace Modules\Auth\DTOs;

use Illuminate\Validation\Rules\Password;
use WendellAdriel\ValidatedDTO\Casting\BooleanCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

final class UpdateuserDTO extends ValidatedDTO
{
    public ?string $name;

    public ?string $login;

    public ?string $email;

    public ?string $current_password;

    public ?string $password;

    public bool $active;

    protected function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:4', 'max:255'],
            'login' => ['sometimes', 'string', 'min:4', 'max:20', 'alpha_dash', 'unique:users,login,' . request()->route('uuid') . ',uuid'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . request()->route('uuid') . ',uuid', 'max:255'],
            'current_password' => ['required_with:password', 'string'],
            'password' => [
                'sometimes',
                Password::min(8)->max(255),
                'confirmed',
            ],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'active' => new BooleanCast(),
        ];
    }
}
