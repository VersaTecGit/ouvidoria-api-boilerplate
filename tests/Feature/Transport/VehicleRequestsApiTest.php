<?php

declare(strict_types=1);

namespace Tests\Feature\Transport;

use Illuminate\Http\Response;
use Modules\Auth\Models\User;
use Modules\Auth\Support\Permissions;
use Modules\Transport\Models\VehicleRequest;
use Modules\Transport\Models\VehicleTrip;
use Modules\Transport\Support\VehicleRequestPriority;
use Modules\Transport\Support\VehicleRequestStatus;
use Modules\Transport\Support\VehicleTripStatus;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Auth\Helpers\UsersHelper;
use Tests\Feature\Transport\Helpers\VehicleRequestsHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class VehicleRequestsApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_a_list_of_vehicle_requests(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_VEHICLE_REQUESTS->value]);

        foreach (range(1, 10) as $number) {
            VehicleRequestsHelper::createTestVehicleRequest();
        }

        $response = $this->getJson(
            '/api/v1/vehicles/requests',
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

    public function test_should_return_vehicle_requests_page_2(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_VEHICLE_REQUESTS->value]);

        foreach (range(1, 30) as $number) {
            VehicleRequestsHelper::createTestVehicleRequest();
        }

        $response = $this->getJson(
            '/api/v1/vehicles/requests?page=2',
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

    public function test_should_return_vehicle_requests_with_total_per_page(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_VEHICLE_REQUESTS->value]);

        foreach (range(1, 30) as $number) {
            VehicleRequestsHelper::createTestVehicleRequest();
        }

        $response = $this->getJson(
            '/api/v1/vehicles/requests?per_page=5',
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

    public function test_should_return_vehicle_requests_with_filter(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_VEHICLE_REQUESTS->value]);

        foreach (range(1, 30) as $number) {
            VehicleRequestsHelper::createTestVehicleRequest();
        }

        $vehicleRequest = VehicleRequest::create(VehicleRequestsHelper::dumbVehicleRequestData());

        $response = $this->getJson(
            '/api/v1/vehicles/requests?search=' . $vehicleRequest->protocol_number,
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

    public function test_should_create_new_vehicle_request(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_VEHICLE_REQUESTS->value]);
        $user = UsersHelper::createTestUser();

        $response = $this->postJson(
            '/api/v1/vehicles/requests',
            [
                'requester_id' => $user->uuid,
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
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'id',
                'requester',
                'origin',
                'destination',
                'waypoints',
                'departure_at',
                'return_at',
                'priority',
                'justification',
                'status',
                'protocol_number',
                'vehicle',
                'driver',
                'response',
                'attachments',
                'user_passengers',
                'created_at',
                'updated_at',
            ]);

        $this->assertTrue(
            VehicleRequest::where('protocol_number', $response->json('protocol_number'))
                ->exists()
        );
    }

    public function test_should_validate_create_new_vehicle_request(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_VEHICLE_REQUESTS->value]);

        $response = $this->postJson(
            '/api/v1/vehicles/requests',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'requester_id',
                'origin',
                'destination',
                'departure_at',
                'priority',
                'justification',
            ]);
    }

    public function test_should_return_vehicle_requests_by_uuid(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLE_REQUESTS->value]);

        $vehicleRequest = VehicleRequestsHelper::createTestVehicleRequest();

        $response = $this->getJson(
            "/api/v1/vehicles/requests/{$vehicleRequest->uuid}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'requester',
                'origin',
                'destination',
                'waypoints',
                'departure_at',
                'return_at',
                'priority',
                'justification',
                'status',
                'protocol_number',
                'vehicle',
                'driver',
                'response',
                'attachments',
                'user_passengers',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_should_return_404_when_not_exists_vehicle_requests(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLE_REQUESTS->value]);

        $response = $this->getJson(
            '/api/v1/vehicles/requests/00000000-0000-0000-0000-000000000000',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_update_vehicle_request(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLE_REQUESTS->value]);

        $user = UsersHelper::createTestUser();
        $vehicleRequest = VehicleRequestsHelper::createTestVehicleRequest($user);
        $vehicleRequest->update(['status' => VehicleRequestStatus::PENDING->value]);

        $response = $this->putJson(
            "/api/v1/vehicles/requests/{$vehicleRequest->uuid}",
            [
                'status' => VehicleRequestStatus::APPROVED->value,
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['status' => VehicleRequestStatus::APPROVED->value]);

        $this->assertTrue(
            VehicleTrip::where('vehicle_request_id', $vehicleRequest->id)
                ->where('status', VehicleTripStatus::NOT_STARTED->value)
                ->exists()
        );
    }

    public function test_should_add_travel_allowances_on_update(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLE_REQUESTS->value]);

        $user = UsersHelper::createTestUser();
        $vehicleRequest = VehicleRequestsHelper::createTestVehicleRequest($user);

        $payload = [
            'travel_allowances' => [
                [
                    'beneficiary_type' => 'user',
                    'beneficiary_id' => $user->uuid,
                    'purpose' => 'Viagem institucional',
                    'days_requested' => 2.5,
                    'unit_value' => 100.0,
                ],
            ],
        ];

        $response = $this->putJson(
            "/api/v1/vehicles/requests/{$vehicleRequest->uuid}",
            $payload,
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('vehicle_request_travel_allowances', [
            'vehicle_request_id' => $vehicleRequest->id,
            'beneficiary_type' => User::class,
            'beneficiary_id' => $user->id,
            'days_requested' => 2.5,
            'unit_value' => 100.0,
        ]);
    }

    public function test_should_update_existing_travel_allowance(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLE_REQUESTS->value]);

        $user = UsersHelper::createTestUser();
        $vehicleRequest = VehicleRequestsHelper::createTestVehicleRequest($user);

        $allowance = $vehicleRequest->travelAllowances()->create([
            'beneficiary_type' => User::class,
            'beneficiary_id' => $user->id,
            'purpose' => 'Viagem inicial',
            'days_requested' => 1,
            'unit_value' => 100,
        ]);

        $payload = [
            'travel_allowances' => [
                [
                    'beneficiary_type' => 'user',
                    'beneficiary_id' => $user->uuid,
                    'purpose' => 'Viagem ajustada',
                    'days_requested' => 3,
                    'unit_value' => 120.0,
                ],
            ],
        ];

        $response = $this->putJson(
            "/api/v1/vehicles/requests/{$vehicleRequest->uuid}",
            $payload,
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('vehicle_request_travel_allowances', [
            'id' => $allowance->id,
            'beneficiary_type' => User::class,
            'beneficiary_id' => $user->id,
            'purpose' => 'Viagem ajustada',
            'days_requested' => 3.0,
            'unit_value' => 120.0,
        ]);
    }

    public function test_should_delete_removed_travel_allowances(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLE_REQUESTS->value]);

        $user = UsersHelper::createTestUser();
        $vehicleRequest = VehicleRequestsHelper::createTestVehicleRequest($user);

        $vehicleRequest->travelAllowances()->createMany([
            [
                'beneficiary_type' => User::class,
                'beneficiary_id' => $user->id,
                'purpose' => 'Viagem antiga',
                'days_requested' => 1,
                'unit_value' => 100,
            ],
            [
                'beneficiary_type' => User::class,
                'beneficiary_id' => $user->id,
                'purpose' => 'Viagem do usuário',
                'days_requested' => 2,
                'unit_value' => 80,
            ],
        ]);

        $payload = ['travel_allowances' => []];

        $response = $this->putJson(
            "/api/v1/vehicles/requests/{$vehicleRequest->uuid}",
            $payload,
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEquals(0, $vehicleRequest->travelAllowances()->count());
    }

    public function test_should_reject_invalid_travel_allowance_payload(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLE_REQUESTS->value]);
        $vehicleRequest = VehicleRequestsHelper::createTestVehicleRequest();

        $payload = [
            'travel_allowances' => [
                [
                    'beneficiary_type' => 'invalid_type',
                    'beneficiary_id' => '00000000-0000-0000-0000-000000000000',
                    'days_requested' => -1,
                    'unit_value' => -50,
                ],
            ],
        ];

        $response = $this->putJson(
            "/api/v1/vehicles/requests/{$vehicleRequest->uuid}",
            $payload,
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'beneficiary_type',
                'purpose',
                'days_requested',
                'unit_value',
            ]);
    }

    public function test_should_delete_vehicle_request(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::DELETE_VEHICLE_REQUESTS->value]);

        $vehicleRequest = VehicleRequestsHelper::createTestVehicleRequest();

        $response = $this->deleteJson(
            "/api/v1/vehicles/requests/{$vehicleRequest->uuid}",
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
