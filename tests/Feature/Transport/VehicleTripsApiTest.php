<?php

declare(strict_types=1);

namespace Tests\Feature\Transport;

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Modules\Transport\Models\VehicleTrip;
use Modules\Transport\Support\VehicleTripStatus;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Auth\Helpers\UsersHelper;
use Tests\Feature\Transport\Helpers\VehicleTripsHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class VehicleTripsApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_a_list_of_vehicle_trips(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_VEHICLE_TRIPS->value]);

        foreach (range(1, 10) as $number) {
            VehicleTripsHelper::createTestVehicleTrip();
        }

        $response = $this->getJson(
            '/api/v1/vehicles/trips',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data', 'links', 'meta']);

        $responseData = $response->json('data');
        $responseMeta = $response->json('meta');

        $this->assertIsArray($responseData);
        $this->assertCount(10, $responseData);

        $this->assertEquals(1, $responseMeta['current_page']);
        $this->assertEquals(1, $responseMeta['last_page']);
        $this->assertEquals(20, $responseMeta['per_page']);
        $this->assertEquals(10, $responseMeta['total']);
    }

    public function test_should_return_vehicle_trips_page_2(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_VEHICLE_TRIPS->value]);

        foreach (range(1, 30) as $number) {
            VehicleTripsHelper::createTestVehicleTrip();
        }

        $response = $this->getJson(
            '/api/v1/vehicles/trips?page=2',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data', 'links', 'meta']);

        $responseData = $response->json('data');
        $responseMeta = $response->json('meta');

        $this->assertIsArray($responseData);
        $this->assertCount(10, $responseData);

        $this->assertEquals(2, $responseMeta['current_page']);
        $this->assertEquals(2, $responseMeta['last_page']);
        $this->assertEquals(20, $responseMeta['per_page']);
        $this->assertEquals(30, $responseMeta['total']);
    }

    public function test_should_return_vehicle_trips_with_total_per_page(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_VEHICLE_TRIPS->value]);

        foreach (range(1, 30) as $number) {
            VehicleTripsHelper::createTestVehicleTrip();
        }

        $response = $this->getJson(
            '/api/v1/vehicles/trips?per_page=5',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data', 'links', 'meta']);

        $responseData = $response->json('data');
        $responseMeta = $response->json('meta');

        $this->assertIsArray($responseData);
        $this->assertCount(5, $responseData);

        $this->assertEquals(1, $responseMeta['current_page']);
        $this->assertEquals(6, $responseMeta['last_page']);
        $this->assertEquals(5, $responseMeta['per_page']);
        $this->assertEquals(30, $responseMeta['total']);
    }

    public function test_should_return_vehicle_trips_with_filter(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_VEHICLE_TRIPS->value]);

        foreach (range(1, 30) as $number) {
            VehicleTripsHelper::createTestVehicleTrip();
        }

        $vehicleTrip = VehicleTrip::create(VehicleTripsHelper::dumbVehicleTripData());

        $response = $this->getJson(
            '/api/v1/vehicles/trips?search=' . $vehicleTrip->vehicleRequest->protocol_number,
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data', 'links', 'meta']);

        $responseData = $response->json('data');
        $responseMeta = $response->json('meta');

        $this->assertIsArray($responseData);
        $this->assertCount(1, $responseData);

        $this->assertEquals(1, $responseMeta['current_page']);
        $this->assertEquals(1, $responseMeta['last_page']);
        $this->assertEquals(20, $responseMeta['per_page']);
        $this->assertEquals(1, $responseMeta['total']);
    }

    public function test_should_return_vehicle_trips_by_uuid(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLE_TRIPS->value]);

        $vehicleTrip = VehicleTripsHelper::createTestVehicleTrip();

        $response = $this->getJson(
            "/api/v1/vehicles/trips/{$vehicleTrip->uuid}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'vehicle_request',
                'status',
                'started_at',
                'finished_at',
                'occurrences',
                'user_passengers',
                'user_passengers',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_should_return_404_when_not_exists_vehicle_trips(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLE_TRIPS->value]);

        $response = $this->getJson(
            '/api/v1/vehicles/trips/00000000-0000-0000-0000-000000000000',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_update_vehicle_trip(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLE_TRIPS->value]);

        $vehicleTrip = VehicleTripsHelper::createTestVehicleTrip();

        $response = $this->putJson(
            "/api/v1/vehicles/trips/{$vehicleTrip->uuid}",
            [
                'status' => VehicleTripStatus::IN_PROGRESS->value,
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['status' => VehicleTripStatus::IN_PROGRESS->value]);
    }

    public function test_should_update_user_passengers(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLE_TRIPS->value]);

        $vehicleTrip = VehicleTripsHelper::createTestVehicleTrip();
        $user = UsersHelper::createTestUser();

        $response = $this->putJson(
            "/api/v1/vehicles/trips/{$vehicleTrip->uuid}",
            [
                'user_passengers' => [
                    [
                        'id' => $user->uuid,
                        'was_present' => true,
                        'absence_reason' => null,
                    ],
                ],
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('vehicle_trip_passengers', [
            'vehicle_trip_id' => $vehicleTrip->id,
            'vehicle_trip_passenger_id' => $user->id,
            'vehicle_trip_passenger_type' => $user->getMorphClass(),
            'was_present' => true,
            'absence_reason' => null,
        ]);
    }

    public function test_should_update_existing_passenger_pivot_without_detaching(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLE_TRIPS->value]);

        $vehicleTrip = VehicleTripsHelper::createTestVehicleTrip();
        $user = UsersHelper::createTestUser();

        $vehicleTrip->userPassengers()->attach($user->id, [
            'was_present' => true,
            'absence_reason' => null,
        ]);

        $response = $this->putJson(
            "/api/v1/vehicles/trips/{$vehicleTrip->uuid}",
            [
                'user_passengers' => [
                    [
                        'id' => $user->uuid,
                        'was_present' => false,
                        'absence_reason' => 'Saiu mais cedo',
                    ],
                ],
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('vehicle_trip_passengers', [
            'vehicle_trip_id' => $vehicleTrip->id,
            'vehicle_trip_passenger_id' => $user->id,
            'was_present' => false,
            'absence_reason' => 'Saiu mais cedo',
        ]);

        $this->assertEquals(
            1,
            $vehicleTrip->userPassengers()->count()
        );
    }

    public function test_should_delete_vehicle_trip(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::DELETE_VEHICLE_TRIPS->value]);

        $vehicleTrip = VehicleTripsHelper::createTestVehicleTrip();

        $response = $this->deleteJson(
            "/api/v1/vehicles/trips/{$vehicleTrip->uuid}",
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
