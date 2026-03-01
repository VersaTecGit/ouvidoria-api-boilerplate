<?php

declare(strict_types=1);

namespace Modules\Transport\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Common\Core\Resources\Concerns\LoadsRelationsIfMissing;

final class VehicleTripResource extends JsonResource
{
    use LoadsRelationsIfMissing;

    public function toArray(Request $request): array
    {
        $vehicleRequest = $this->loadIfMissing('vehicleRequest');
        $userPassengers = $this->loadIfMissing('userPassengers');

        return [
            'id' => $this->uuid,
            'vehicle_request' => VehicleRequestResource::make($vehicleRequest),
            'status' => $this->status,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'occurrences' => $this->occurrences,
            'user_passengers' => $userPassengers->map(fn ($passenger) => [
                'id' => $passenger->uuid,
                'name' => $passenger->name,
                'cpf' => $passenger->cpf,
                'birth_date' => $passenger->birth_date,
                'was_present' => $passenger->pivot->was_present,
                'absence_reason' => $passenger->pivot->absence_reason,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
