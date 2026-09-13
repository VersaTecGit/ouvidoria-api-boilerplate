<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Common\Core\Resources\Concerns\LoadsRelationsIfMissing;

/**
 * Full payload for authenticated staff: manifestant data, internal notes and
 * attachments included. Never serve this to an anonymous caller — the public
 * endpoint has its own minimal resource (Fase 4).
 */
final class ManifestationResource extends JsonResource
{
    use LoadsRelationsIfMissing;

    public function toArray(Request $request): array
    {
        $agency = $this->loadIfMissing('destinationAgency');
        $unit = $this->loadIfMissing('unit');
        $respondedBy = $this->loadIfMissing('respondedBy');
        $logs = $this->loadIfMissing('logs');

        return [
            'id' => $this->uuid,
            'protocol_number' => $this->protocol_number,
            'type' => $this->type,
            'status' => $this->status,
            'destination_agency' => $agency ? [
                'id' => $agency->uuid,
                'name' => $agency->name,
            ] : null,
            'unit' => $unit ? [
                'id' => $unit->uuid,
                'name' => $unit->name,
            ] : null,
            'subject' => $this->subject,
            'description' => $this->description,
            'occurrence_place' => $this->occurrence_place,
            'is_anonymous' => $this->is_anonymous,
            'manifestant' => $this->is_anonymous ? null : [
                'name' => $this->manifestant_name,
                'email' => $this->manifestant_email,
                'phone' => $this->manifestant_phone,
                'document' => $this->manifestant_document,
                'address' => $this->manifestant_address,
            ],
            'parecer' => $this->parecer,
            'responded_by' => $respondedBy ? [
                'id' => $respondedBy->uuid,
                'name' => $respondedBy->name,
            ] : null,
            'responded_at' => $this->responded_at,
            'attachments' => $this->getMedia('attachments')->map(fn ($media) => [
                'type' => $media->mime_type,
                'url' => $media->getTemporaryUrl(now()->addHours(2)),
            ]),
            'logs' => ManifestationLogResource::collection($logs),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
