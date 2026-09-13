<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Common\Core\Resources\Concerns\LoadsRelationsIfMissing;

/**
 * Internal timeline entry. Carries `is_public` and the author, so it is for
 * authenticated staff only — the citizen timeline gets its own resource
 * in the public endpoint.
 */
final class ManifestationLogResource extends JsonResource
{
    use LoadsRelationsIfMissing;

    public function toArray(Request $request): array
    {
        $author = $this->loadIfMissing('author');

        return [
            'id' => $this->uuid,
            'content' => $this->content,
            'is_public' => $this->is_public,
            'status' => $this->status,
            'author' => $author ? [
                'id' => $author->uuid,
                'name' => $author->name,
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
