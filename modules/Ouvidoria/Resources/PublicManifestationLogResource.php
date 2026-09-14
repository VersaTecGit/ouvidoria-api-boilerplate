<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One entry of the citizen's "Histórico de Andamento". No id, no author,
 * no `is_public` flag: the caller only ever receives entries that are
 * public, and must not learn who wrote them.
 */
final class PublicManifestationLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'content' => $this->content,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
