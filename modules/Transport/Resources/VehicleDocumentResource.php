<?php

declare(strict_types=1);

namespace Modules\Transport\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class VehicleDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'type' => $this->type,
            'attributes' => $this->attributes,
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
