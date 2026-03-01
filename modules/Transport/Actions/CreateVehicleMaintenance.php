<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Transport\DTOs\CreateVehicleMaintenanceDTO;
use Modules\Transport\Models\VehicleMaintenance;

final readonly class CreateVehicleMaintenance
{
    public function __construct(
        private FetchVehicle $fetchVehicle
    ) {}

    public function handle(CreateVehicleMaintenanceDTO $dto, string $uuid): VehicleMaintenance
    {
        $vehicle = $this->fetchVehicle->handle($uuid);

        DB::beginTransaction();
        try {
            $vehicleMaintenance = $dto->toModel(VehicleMaintenance::class);

            $vehicleMaintenance->vehicle_id = $vehicle->id;
            $vehicleMaintenance->save();

            foreach ($dto->files as $file) {
                $vehicleMaintenance->addMediaFromDisk($file->key, 'central')
                    ->usingFileName($file->uuid . '.' . $file->extension)
                    ->toMediaCollection('files');
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $vehicleMaintenance;
    }
}
