<?php

declare(strict_types=1);

namespace Modules\Transport\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Common\Core\Resources\Concerns\LoadsRelationsIfMissing;
use Modules\Transport\Support\VehicleRequestStatus;

final class VehicleResource extends JsonResource
{
    use LoadsRelationsIfMissing;

    public function toArray(Request $request): array
    {
        $departureAt = $request->query('departure_at');
        $returnAt = $request->query('return_at');

        $data = [
            'id' => $this->uuid,
            'required_license_categories' => $this->required_license_categories,
            'plate' => $this->plate,
            'model' => $this->model,
            'brand' => $this->brand,
            'capacity' => $this->capacity,
            'color' => $this->color,
            'fuels' => $this->fuels,
            'manufacture_year' => $this->manufacture_year,
            'renavam' => $this->renavam,
            'chassis_number' => $this->chassis_number,
            'type' => $this->type,
            'other_type' => $this->other_type,
            'status' => $this->status,
            'pictures' => $this->getMedia('pictures')->map(fn ($media) => [
                'name' => $media->file_name,
                'url' => $media->getTemporaryUrl(now()->addHours(2)),
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($departureAt && $returnAt) {
            $data['is_available'] = $this->checkAvailabilityBetween($departureAt, $returnAt);
        }

        return $data;
    }

    private function checkAvailabilityBetween(string $begin, string $end): bool
    {
        if ($this->relationLoaded('vehicleRequests')) {
            return $this->vehicleRequests->isEmpty();
        }

        return ! $this->vehicleRequests()
            ->where('status', VehicleRequestStatus::APPROVED->value)
            ->where('departure_at', '<', $end)
            ->where('return_at', '>', $begin)
            ->exists();
    }
}
