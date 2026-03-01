<?php

declare(strict_types=1);

namespace Modules\Transport\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Common\Core\Resources\Concerns\LoadsRelationsIfMissing;

final class VehicleRequestResource extends JsonResource
{
    use LoadsRelationsIfMissing;

    public function toArray(Request $request): array
    {
        $requester = $this->loadIfMissing('requester');
        $vehicle = $this->loadIfMissing('vehicle');
        $driver = $this->loadIfMissing('driver');
        $userPassengers = $this->loadIfMissing('userPassengers');
        $travelAllowances = $this->loadIfMissing('travelAllowances');

        return [
            'id' => $this->uuid,
            'requester' => [
                'id' => $requester->uuid,
                'name' => $requester->name,
            ],
            'origin' => $this->origin,
            'destination' => $this->destination,
            'waypoints' => $this->waypoints,
            'departure_at' => $this->departure_at,
            'return_at' => $this->return_at,
            'priority' => $this->priority,
            'justification' => $this->justification,
            'status' => $this->status,
            'protocol_number' => $this->protocol_number,
            'vehicle' => $vehicle ? VehicleResource::make($vehicle) : null,
            'driver' => $driver ? [
                'id' => $driver->uuid,
                'name' => $driver->name,
                'driver' => $driver->driver,
            ] : null,
            'response' => $this->response,
            'attachments' => $this->getMedia('attachments')->map(fn ($media) => [
                'type' => $media->mime_type,
                'url' => $media->getTemporaryUrl(now()->addHours(2)),
            ]),
            'user_passengers' => $userPassengers->map(fn ($passenger) => [
                'id' => $passenger->uuid,
                'name' => $passenger->name,
            ]),
            'travel_allowances' => $travelAllowances->map(fn ($allowance) => [
                'beneficiary_type' => 'user',
                'beneficiary_id' => $allowance->beneficiary->uuid,
                'purpose' => $allowance->purpose,
                'days_requested' => $allowance->days_requested,
                'unit_value' => $allowance->unit_value,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
