<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Minimal unit payload for the unauthenticated manifestation form.
 *
 * Deliberately exposes only what the destination select needs. Do not add
 * fields here: this resource is served to anonymous callers.
 */
final class PublicUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
        ];
    }
}
