<?php

declare(strict_types=1);

namespace Tests\Feature\Transport;

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Modules\Transport\Models\VehicleRefuel;
use Modules\Transport\Support\VehicleFuel;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Transport\Helpers\VehicleRefuelsHelper;
use Tests\Feature\Transport\Helpers\VehiclesHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class VehicleRefuelsApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_a_list_of_vehicle_refuels(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        foreach (range(1, 10) as $number) {
            VehicleRefuelsHelper::createTestVehicleRefuel($vehicle);
        }

        $response = $this->getJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/refuels',
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

    public function test_should_return_vehicle_refuels_page_2(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        foreach (range(1, 30) as $number) {
            VehicleRefuelsHelper::createTestVehicleRefuel($vehicle);
        }

        $response = $this->getJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/refuels?page=2',
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

    public function test_should_return_vehicle_refuels_with_total_per_page(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        foreach (range(1, 30) as $number) {
            VehicleRefuelsHelper::createTestVehicleRefuel($vehicle);
        }

        $response = $this->getJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/refuels?per_page=5',
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

    public function test_should_create_new_vehicle_refuel(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        $response = $this->postJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/refuels',
            [
                'refueled_at' => '2024-01-15 10:00:00',
                'odometer' => 15000,
                'liters' => 50.5,
                'price_per_liter' => 4.29,
                'total_value' => 216.55,
                'fuel_type' => VehicleFuel::GASOLINE->value,
                'station_location' => [
                    'address' => '123 Main St, Anytown, USA',
                    'lat' => 40.712776,
                    'lng' => -74.005974,
                ],
                'consumption' => 12.5,
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
                'refueled_at',
                'odometer',
                'liters',
                'price_per_liter',
                'total_value',
                'fuel_type',
                'station_location',
                'consumption',
                'created_at',
                'updated_at',
            ]);

        $this->assertTrue(
            VehicleRefuel::where('vehicle_id', $vehicle->id)
                ->where('refueled_at', '2024-01-15 10:00:00')
                ->where('odometer', 15000)
                ->where('liters', 50.5)
                ->where('price_per_liter', 4.29)
                ->where('total_value', 216.55)
                ->where('fuel_type', VehicleFuel::GASOLINE->value)
                ->where('station_location->address', '123 Main St, Anytown, USA')
                ->where('station_location->lat', 40.712776)
                ->where('station_location->lng', -74.005974)
                ->where('consumption', 12.5)
                ->exists()
        );
    }

    public function test_should_validate_create_new_vehicle_refuel(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        $response = $this->postJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/refuels',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'refueled_at',
                'odometer',
                'liters',
                'price_per_liter',
                'total_value',
                'fuel_type',
            ]);
    }

    public function test_should_return_vehicle_refuels_by_uuid(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);

        $vehicleRefuel = VehicleRefuelsHelper::createTestVehicleRefuel();

        $response = $this->getJson(
            "/api/v1/vehicles/refuels/{$vehicleRefuel->uuid}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'refueled_at',
                'odometer',
                'liters',
                'price_per_liter',
                'total_value',
                'fuel_type',
                'station_location',
                'consumption',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_should_return_404_when_not_exists_vehicle_refuels(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);

        $response = $this->getJson(
            '/api/v1/vehicles/refuels/00000000-0000-0000-0000-000000000000',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_update_vehicle_refuel(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLES->value]);

        $vehicleRefuel = VehicleRefuelsHelper::createTestVehicleRefuel();

        $response = $this->putJson(
            "/api/v1/vehicles/refuels/{$vehicleRefuel->uuid}",
            [
                'odometer' => 20000,
                'liters' => 60.0,
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'odometer' => 20000,
                'liters' => 60.0,
            ]);
    }

    public function test_should_delete_vehicle_refuel(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLES->value]);

        $vehicleRefuel = VehicleRefuelsHelper::createTestVehicleRefuel();

        $response = $this->deleteJson(
            "/api/v1/vehicles/refuels/{$vehicleRefuel->uuid}",
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
