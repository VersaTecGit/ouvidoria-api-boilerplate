<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\DTOs;

use Illuminate\Validation\Rule;
use Modules\Ouvidoria\Support\ManifestationStatus;
use WendellAdriel\ValidatedDTO\Casting\BooleanCast;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

/**
 * A timeline entry written by the attendant. `is_public` decides whether the
 * citizen reads it: it defaults to false, so an entry only becomes visible
 * when someone deliberately says so.
 */
class CreateManifestationLogDTO extends ValidatedDTO
{
    public string $content;

    public bool $is_public;

    public ?ManifestationStatus $status;

    protected function rules(): array
    {
        return [
            'content' => ['required', 'string'],
            'is_public' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(ManifestationStatus::toArray())],
        ];
    }

    protected function defaults(): array
    {
        return [
            'is_public' => false,
        ];
    }

    protected function casts(): array
    {
        return [
            'content' => new StringCast(),
            'is_public' => new BooleanCast(),
            'status' => new EnumCast(ManifestationStatus::class),
        ];
    }
}
