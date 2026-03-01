<?php

declare(strict_types=1);

namespace Modules\Auth\DTOs;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

final class LoginWithTwoFactorDTO extends ValidatedDTO
{
    public string $login;

    public string $password;

    public string $uuid;

    public string $code;

    protected function rules(): array
    {
        return [
            'uuid' => ['required', 'string'],
            'code' => ['required', 'string'],
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
