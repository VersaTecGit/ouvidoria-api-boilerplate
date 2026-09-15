<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\DTOs;

use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

final class CreatePublicManifestationSignedStorageUrlDTO extends ValidatedDTO
{
    public string $content_type;

    public string $file_name;

    public int $file_size;

    protected function rules(): array
    {
        return [
            'content_type' => ['required', 'string'],
            'file_name' => ['required', 'string', 'max:255'],
            'file_size' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'content_type' => new StringCast(),
            'file_name' => new StringCast(),
            'file_size' => new IntegerCast(),
        ];
    }
}
