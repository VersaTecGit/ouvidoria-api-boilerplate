<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Resources;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * What the anonymous citizen gets back, on creation and when consulting by
 * protocol. The protocol is a bearer secret that travels in screenshots and
 * shared phones, so everything past this list is over-exposure: no id,
 * subject, description, place, agency, unit, manifestant, attachments,
 * parecer, responder, nor any non-public log. Do not add fields here.
 */
final class PublicManifestationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Always the `publicLogs` relation, never `logs`: internal notes must not be loaded at all.
        $this->loadMissing(['publicLogs' => fn (HasMany $query) => $query->oldest()->orderBy('id')]);

        return [
            'protocol_number' => $this->protocol_number,
            'type' => $this->type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'logs' => PublicManifestationLogResource::collection($this->publicLogs),
        ];
    }
}
