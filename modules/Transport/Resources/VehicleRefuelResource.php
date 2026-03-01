<?php

declare(strict_types=1);

namespace Modules\Transport\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class VehicleRefuelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'refueled_at' => $this->refueled_at,
            'odometer' => $this->odometer,
            'liters' => $this->liters,
            'price_per_liter' => $this->price_per_liter,
            'total_value' => $this->total_value,
            'fuel_type' => $this->fuel_type,
            'station_location' => $this->station_location,
            'consumption' => $this->consumption,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
