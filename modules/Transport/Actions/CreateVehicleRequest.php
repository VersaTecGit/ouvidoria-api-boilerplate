<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Transport\DTOs\CreateVehicleRequestDTO;
use Modules\Transport\Models\VehicleRequest;

final readonly class CreateVehicleRequest
{
    public function handle(CreateVehicleRequestDTO $dto): VehicleRequest
    {
        DB::beginTransaction();
        try {
            $vehicleRequest = $dto->toModel(VehicleRequest::class);
            $vehicleRequest->protocol_number = VehicleRequest::generateProtocolNumber();

            $vehicleRequest->save();

            $this->handleUserPassengers($vehicleRequest, $dto->user_passengers);

            foreach ($dto->attachments as $attachment) {
                $vehicleRequest->addMediaFromDisk($attachment->key, 'central')
                    ->usingFileName($attachment->uuid . '.' . $attachment->extension)
                    ->toMediaCollection('attachments');
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $vehicleRequest;
    }

    private function handleUserPassengers(VehicleRequest $vehicleRequest, array $userPassengers): void
    {
        $vehicleRequest->userPassengers()->sync($userPassengers);
    }
}
