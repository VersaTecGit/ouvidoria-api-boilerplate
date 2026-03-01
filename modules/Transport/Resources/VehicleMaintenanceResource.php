<?php

declare(strict_types=1);

namespace Modules\Transport\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class VehicleMaintenanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'type' => $this->type,
            'service' => $this->service,
            'other_service' => $this->other_service,
            'performed_at' => $this->performed_at,
            'scheduled_at' => $this->scheduled_at,
            'workshop' => $this->workshop,
            'description' => $this->description,
            'cost' => $this->cost,
            'status' => $this->status,
            'files' => $this->getMedia('files')->map(fn ($media) => [
                'name' => $media->file_name,
                'type' => $media->mime_type,
                'url' => $media->getTemporaryUrl(now()->addHours(2)),
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
