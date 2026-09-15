<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Common\Core\Resources\Concerns\LoadsRelationsIfMissing;

/**
 * Listing payload for the staff datatable. Omits description, manifestant
 * data, attachments and the timeline: a list view has no use for them, and
 * each row would otherwise cost a temporary URL per attachment.
 */
final class ManifestationListResource extends JsonResource
{
    use LoadsRelationsIfMissing;

    public function toArray(Request $request): array
    {
        $agency = $this->loadIfMissing('destinationAgency');
        $unit = $this->loadIfMissing('unit');

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
            'occurrence_place' => $this->occurrence_place,
            'is_anonymous' => $this->is_anonymous,
            'manifestant_name' => $this->is_anonymous ? null : $this->manifestant_name,
            'responded_at' => $this->responded_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
