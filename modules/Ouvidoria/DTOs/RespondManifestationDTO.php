<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\DTOs;

use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

/**
 * The attendant's final opinion. Saving it always produces a public timeline
 * entry — the citizen has no other channel — and closes the manifestation
 * as `respondida`.
 */
class RespondManifestationDTO extends ValidatedDTO
{
    public string $parecer;

    protected function rules(): array
    {
        return [
            'parecer' => ['required', 'string'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'parecer' => new StringCast(),
        ];
    }
}
