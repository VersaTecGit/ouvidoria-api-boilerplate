<?php

declare(strict_types=1);

namespace Tests\Feature\Transport\Helpers;

use Modules\Auth\Models\User;
use Modules\Transport\Models\VehicleRequest;
use Modules\Transport\Support\VehicleRequestPriority;
use Modules\Transport\Support\VehicleRequestStatus;
use Tests\Feature\Auth\Helpers\UsersHelper;

class VehicleRequestsHelper
{
    public static function createTestVehicleRequest(): VehicleRequest
    {
        $user = UsersHelper::createTestUser();

        $vehicleRequest = new VehicleRequest([
            'requester_id' => $user->id,
            'origin' => [
                'address' => fake()->address(),
                'lat' => fake()->latitude(),
                'lng' => fake()->longitude(),
            ],
            'destination' => [
                'address' => fake()->address(),
                'lat' => fake()->latitude(),
                'lng' => fake()->longitude(),
            ],
            'waypoints' => [
                [
                    'address' => fake()->address(),
                    'lat' => fake()->latitude(),
                    'lng' => fake()->longitude(),
                ],
            ],
            'departure_at' => now()->addDay(),
            'return_at' => now()->addDays(2),
            'priority' => fake()->randomElement(VehicleRequestPriority::toArray()),
            'justification' => 'Business meeting',
            'status' => fake()->randomElement(VehicleRequestStatus::toArray()),
            'protocol_number' => VehicleRequest::generateProtocolNumber(),
            'attachments' => [],
            'user_passengers' => [],
        ]);

        $vehicleRequest->save();

        return $vehicleRequest;
    }

    public static function dumbVehicleRequestData(?User $user = null): array
    {
        $user ??= UsersHelper::createTestUser();

        return [
            'requester_id' => $user->id,
            'origin' => [
                'address' => '123 Main St, City, Country',
                'lat' => 12.345678,
                'lng' => 98.7654321,
            ],
            'destination' => [
                'address' => '456 Elm St, City, Country',
                'lat' => 23.456789,
                'lng' => 87.6543210,
            ],
            'waypoints' => [
                [
                    'address' => '789 Oak St, City, Country',
                    'lat' => 34.567890,
                    'lng' => 76.5432109,
                ],
            ],
            'departure_at' => now()->addDay(),
            'return_at' => now()->addDays(2),
            'priority' => VehicleRequestPriority::HIGH->value,
            'justification' => 'Business meeting',
            'status' => VehicleRequestStatus::PENDING->value,
            'protocol_number' => VehicleRequest::generateProtocolNumber(),
            'attachments' => [],
            'user_passengers' => [],
        ];
    }
}
