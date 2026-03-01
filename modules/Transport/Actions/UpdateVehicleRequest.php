<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Actions\FetchUser;
use Modules\Auth\Actions\LoggedUser;
use Modules\Auth\Models\User;
use Modules\Auth\Support\Permissions;
use Modules\Common\Core\Exceptions\ApiException;
use Modules\Transport\DTOs\TravelAllowanceDTO;
use Modules\Transport\DTOs\UpdateVehicleRequestDTO;
use Modules\Transport\Models\VehicleRequest;
use Modules\Transport\Models\VehicleTrip;
use Modules\Transport\Notifications\VehicleRequestApprovedNotification;
use Modules\Transport\Notifications\VehicleRequestRejectedNotification;
use Modules\Transport\Support\VehicleRequestStatus;
use Modules\Transport\Support\VehicleTripStatus;

final readonly class UpdateVehicleRequest
{
    public function __construct(
        private FetchUser $fetchUser,
        private LoggedUser $loggedUser,
        private FetchVehicle $fetchVehicle,
        private FetchVehicleRequest $fetchVehicleRequest,
    ) {}

    public function handle(string $uuid, UpdateVehicleRequestDTO $dto): VehicleRequest
    {
        $loggedUser = $this->loggedUser->handle();
        $vehicleRequest = $this->fetchVehicleRequest->handle($uuid);

        $hasPermission = $loggedUser->hasPermissionTo(Permissions::EDIT_VEHICLE_REQUESTS->value);
        if (! $hasPermission && $vehicleRequest->requester_id !== $loggedUser->id) {
            throw new ApiException('O usuário não tem permissão para editar esta solicitação!');
        }

        DB::beginTransaction();
        try {
            $updateData = $dto->nullableSafeToArray(VehicleRequest::nullable());
            $updateData = $this->handleUserPassengers($vehicleRequest, $updateData);
            $updateData = $this->handleDriverAndVehicle($updateData, $vehicleRequest);
            $updateData = $this->handleTravelAllowances($vehicleRequest, $updateData);

            $this->handleStatusTransition($vehicleRequest, $updateData);

            $vehicleRequest->fill($updateData);
            $vehicleRequest->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $vehicleRequest;
    }

    protected function handleStatusTransition(VehicleRequest $vehicleRequest, array $updateData): void
    {
        if (! isset($updateData['status'])) {
            return;
        }

        if ($updateData['status'] === $vehicleRequest->status->value) {
            return;
        }

        match ($updateData['status']) {
            VehicleRequestStatus::APPROVED->value => $this->handleApprovation($vehicleRequest),
            VehicleRequestStatus::REJECTED->value => $this->handleRejection($vehicleRequest),
            default => null,
        };
    }

    protected function handleApprovation(VehicleRequest $vehicleRequest): void
    {
        if ($vehicleRequest->vehicleTrip()->exists()) {
            return;
        }

        $trip = VehicleTrip::create([
            'vehicle_request_id' => $vehicleRequest->id,
            'status' => VehicleTripStatus::NOT_STARTED,
        ]);

        foreach ($vehicleRequest->userPassengers as $user) {
            $trip->userPassengers()->attach($user->id, [
                'was_present' => null,
                'absence_reason' => null,
            ]);
        }

        try {
            $vehicleRequest->requester
                ->notify(new VehicleRequestApprovedNotification($vehicleRequest));
        } catch (Exception $e) {
            Log::error('Error sending vehicle request approved notification: ' . $e->getMessage());
        }
    }

    protected function handleRejection(VehicleRequest $vehicleRequest): void
    {
        try {
            $vehicleRequest->requester
                ->notify(new VehicleRequestRejectedNotification($vehicleRequest));
        } catch (Exception $e) {
            Log::error('Error sending vehicle request rejected notification: ' . $e->getMessage());
        }
    }

    protected function handleDriverAndVehicle(array $updateData, VehicleRequest $vehicleRequest): array
    {
        if (! isset($updateData['driver_id'])) {
            return $updateData;
        }

        $driver = $this->fetchUser->handle($updateData['driver_id']);
        $vehicle = $updateData['vehicle_id']
            ? $this->fetchVehicle->handle($updateData['vehicle_id'])
            : $vehicleRequest->vehicle;

        if (! $vehicle) {
            throw new ApiException('Veículo não encontrado para esta solicitação!');
        }

        $totalPassengers = $vehicleRequest->userPassengers()->count() + 1; // +1 for the driver
        if ($vehicle->capacity < $totalPassengers) {
            throw new ApiException('A capacidade do veículo é insuficiente para os passageiros!');
        }

        $hasLicense = collect($driver->driver['cnh_categories'])
            ->intersect($vehicle->required_license_categories)
            ->isNotEmpty();

        if (! $hasLicense) {
            throw new ApiException('O motorista não possui a categoria de CNH necessária!');
        }

        $this->ensureNoConflict($vehicleRequest, $driver->id, $vehicle->id, $updateData);

        $updateData['driver_id'] = $driver->id;
        $updateData['vehicle_id'] = $vehicle->id;

        return $updateData;
    }

    protected function ensureNoConflict(VehicleRequest $current, int $driverId, int $vehicleId, array $updateData): void
    {
        $overlap = fn ($q) => $current->departure_at && $current->return_at
            ? $q->where('departure_at', '<', $current->return_at)
                ->where('return_at', '>', $current->departure_at)
            : ($current->departure_at
                ? $q->where('departure_at', '<', $current->departure_at)
                    ->orWhere(function ($query) use ($current) {
                        $query->where('departure_at', '<', $current->departure_at)
                            ->whereNull('return_at');
                    })
                : $q->where('return_at', '>', $current->return_at));

        $driverConflict = VehicleRequest::whereKeyNot($current->id)
            ->where('driver_id', $driverId)
            ->where('status', VehicleRequestStatus::APPROVED->value)
            ->where($overlap)
            ->exists();

        if ($driverConflict) {
            throw new ApiException('O motorista já possui uma solicitação aprovada para esse período!');
        }

        $vehicleConflict = VehicleRequest::whereKeyNot($current->id)
            ->where('vehicle_id', $vehicleId)
            ->where('status', VehicleRequestStatus::APPROVED->value)
            ->where($overlap)
            ->exists();

        if ($vehicleConflict) {
            throw new ApiException('O veículo já possui uma solicitação aprovada para esse período!');
        }
    }

    private function handleUserPassengers(VehicleRequest $vehicleRequest, array $updateData): array
    {
        $driverId = $updateData['driver_id'] ?? $vehicleRequest->driver_id;
        $driver = $driverId ? $this->fetchUser->handle($updateData['driver_id']) : null;

        if ($driver && $vehicleRequest->driver_id !== $driver->id && $vehicleRequest->userPassengers->contains($driver)) {
            $vehicleRequest->userPassengers()->detach($driver->id);
        }

        if (isset($updateData['user_passengers'])) {
            $userPassengers = collect($updateData['user_passengers'])
                ->filter(fn ($id) => $id !== $driver->id)
                ->values()
                ->all();

            $vehicleRequest->userPassengers()->sync($userPassengers);

            unset($updateData['user_passengers']);
        } else {
            if ($driver) {
                $vehicleRequest->userPassengers()->detach($driver->id);
            }
        }

        return $updateData;
    }

    private function handleTravelAllowances(VehicleRequest $vehicleRequest, array $updateData): array
    {
        if (! isset($updateData['travel_allowances'])) {
            return $updateData;
        }

        $existingAllowances = $vehicleRequest->travelAllowances()->get();

        $incoming = collect($updateData['travel_allowances'])->map(function (TravelAllowanceDTO $dto) {
            $data = $dto->toArray();

            if ($data['beneficiary_type'] === 'user') {
                $model = User::where('uuid', $data['beneficiary_id'])->firstOrFail();
            }

            $data['beneficiary_type'] = get_class($model);
            $data['beneficiary_id'] = $model->id;

            return $data;
        });

        $existingAllowances
            ->reject(fn ($existing) => $incoming->contains(fn ($i) => $i['beneficiary_id'] === $existing->beneficiary_id
                    && $i['beneficiary_type'] === $existing->beneficiary_type))
            ->each(fn ($toDelete) => $toDelete->delete());

        foreach ($incoming as $data) {
            $vehicleRequest->travelAllowances()->updateOrCreate(
                [
                    'beneficiary_type' => $data['beneficiary_type'],
                    'beneficiary_id' => $data['beneficiary_id'],
                ],
                [
                    'purpose' => $data['purpose'],
                    'days_requested' => $data['days_requested'],
                    'unit_value' => $data['unit_value'],
                ]
            );
        }

        unset($updateData['travel_allowances']);

        return $updateData;
    }
}
