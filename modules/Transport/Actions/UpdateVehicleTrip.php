<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Transport\DTOs\UpdateVehicleTripDTO;
use Modules\Transport\Models\VehicleTrip;

final readonly class UpdateVehicleTrip
{
    public function __construct(
        private FetchVehicleTrip $fetchVehicleTrip,
    ) {}

    public function handle(string $uuid, UpdateVehicleTripDTO $dto): VehicleTrip
    {
        $vehicleTrip = $this->fetchVehicleTrip->handle($uuid);

        DB::beginTransaction();

        try {
            $updateData = $dto->nullableSafeToArray(VehicleTrip::nullable());

            $updateData = $this->syncUserPassengers($vehicleTrip, $updateData);

            $vehicleTrip->fill($updateData);
            $vehicleTrip->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $vehicleTrip->refresh();
    }

    private function syncUserPassengers(VehicleTrip $vehicleTrip, array $updateData): array
    {
        if (! isset($updateData['user_passengers'])) {
            return $updateData;
        }

        $vehicleTrip->userPassengers()->syncWithoutDetaching(
            collect($updateData['user_passengers'])
                ->keyBy(fn ($passenger) => $passenger->id)
                ->map(fn ($passenger) => [
                    'was_present' => $passenger->was_present,
                    'absence_reason' => $passenger->absence_reason,
                ])
                ->toArray()
        );

        unset($updateData['user_passengers']);

        return $updateData;
    }
}
