<?php

declare(strict_types=1);

namespace Modules\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Modules\Common\Core\Resources\Concerns\HasMedia;
use Modules\Common\Core\Resources\Concerns\LoadsRelationsIfMissing;
use Modules\Common\Core\Resources\MediaResource;
use Modules\Transport\Support\VehicleRequestStatus;

final class UserResource extends JsonResource
{
    use HasMedia, LoadsRelationsIfMissing;

    public function toArray(Request $request): array
    {
        $departureAt = $request->query('departure_at');
        $returnAt = $request->query('return_at');

        $data = [
            'id' => $this->uuid,
            'name' => $this->name,
            'login' => $this->login,
            'email' => $this->email,
            'roles' => $this->loadIfMissing('roles')->pluck('id'),
            'permissions' => $this->loadIfMissing('roles')->isNotEmpty()
                ? $this->getAllPermissions()->pluck('name')
                : [],
            'active' => $this->active,
            'avatar' => $this->getFirstTemporaryUrl(Carbon::now()->addHours(2), 'avatars', 'large') ?: null,
            $this->mergeWhen($this->shouldIncludeMedia(), [
                'media' => MediaResource::collection($this->getMedia('*')),
            ]),
            'last_login_at' => optional($this->loadIfMissing('latestLogin'))->created_at,
            'driver' => $this->driver,
            'two_factor_confirmed_at' => $this->two_factor_confirmed_at,
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
        if ($this->relationLoaded('vehicleRequestsAsDriver')) {
            return $this->vehicleRequestsAsDriver->isEmpty();
        }

        return ! $this->vehicleRequestsAsDriver()
            ->where('status', VehicleRequestStatus::APPROVED->value)
            ->where('departure_at', '<', $end)
            ->where('return_at', '>', $begin)
            ->exists();
    }
}
