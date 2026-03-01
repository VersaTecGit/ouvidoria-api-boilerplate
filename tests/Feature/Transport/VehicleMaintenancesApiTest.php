<?php

declare(strict_types=1);

namespace Tests\Feature\Transport;

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Modules\Transport\Models\VehicleMaintenance;
use Modules\Transport\Support\VehicleMaintenanceService;
use Modules\Transport\Support\VehicleMaintenanceStatus;
use Modules\Transport\Support\VehicleMaintenanceType;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Transport\Helpers\VehicleMaintenancesHelper;
use Tests\Feature\Transport\Helpers\VehiclesHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class VehicleMaintenancesApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_a_list_of_vehicle_maintenances(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        foreach (range(1, 10) as $number) {
            VehicleMaintenancesHelper::createTestVehicleMaintenance($vehicle);
        }

        $response = $this->getJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/maintenances',
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

    public function test_should_return_vehicle_maintenances_page_2(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        foreach (range(1, 30) as $number) {
            VehicleMaintenancesHelper::createTestVehicleMaintenance($vehicle);
        }

        $response = $this->getJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/maintenances?page=2',
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

    public function test_should_return_vehicle_maintenances_with_total_per_page(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        foreach (range(1, 30) as $number) {
            VehicleMaintenancesHelper::createTestVehicleMaintenance($vehicle);
        }

        $response = $this->getJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/maintenances?per_page=5',
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

    public function test_should_create_new_vehicle_maintenance(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        $response = $this->postJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/maintenances',
            [
                'type' => VehicleMaintenanceType::CORRECTIVE->value,
                'service' => VehicleMaintenanceService::OIL_CHANGE->value,
                'other_service' => null,
                'performed_at' => '2024-05-01 10:00:00',
                'scheduled_at' => null,
                'workshop' => 'AutoFix Workshop',
                'description' => 'Changed engine oil and filter.',
                'cost' => 120.50,
                'status' => VehicleMaintenanceStatus::DONE->value,
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
                'type',
                'service',
                'other_service',
                'performed_at',
                'scheduled_at',
                'workshop',
                'description',
                'cost',
                'status',
                'files',
                'created_at',
                'updated_at',
            ]);

        $this->assertTrue(
            VehicleMaintenance::where('vehicle_id', $vehicle->id)
                ->where('type', VehicleMaintenanceType::CORRECTIVE->value)
                ->where('service', VehicleMaintenanceService::OIL_CHANGE->value)
                ->where('other_service', null)
                ->where('performed_at', '2024-05-01 13:00:00')
                ->where('scheduled_at', null)
                ->where('workshop', 'AutoFix Workshop')
                ->where('description', 'Changed engine oil and filter.')
                ->where('cost', 120.50)
                ->where('status', VehicleMaintenanceStatus::DONE->value)
                ->exists()
        );
    }

    public function test_should_validate_create_new_vehicle_maintenance(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        $response = $this->postJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/maintenances',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'type',
                'service',
                'status',
            ]);
    }

    public function test_should_return_vehicle_maintenances_by_uuid(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);

        $vehicleMaintenance = VehicleMaintenancesHelper::createTestVehicleMaintenance();

        $response = $this->getJson(
            "/api/v1/vehicles/maintenances/{$vehicleMaintenance->uuid}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'type',
                'service',
                'other_service',
                'performed_at',
                'scheduled_at',
                'workshop',
                'description',
                'cost',
                'status',
                'files',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_should_return_404_when_not_exists_vehicle_maintenances(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);

        $response = $this->getJson(
            '/api/v1/vehicles/maintenances/00000000-0000-0000-0000-000000000000',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_update_vehicle_maintenance(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLES->value]);

        $vehicleMaintenance = VehicleMaintenancesHelper::createTestVehicleMaintenance();

        $response = $this->putJson(
            "/api/v1/vehicles/maintenances/{$vehicleMaintenance->uuid}",
            [
                'workshop' => 'Updated Workshop',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'workshop' => 'Updated Workshop',
            ]);
    }

    public function test_should_delete_vehicle_maintenance(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLES->value]);

        $vehicleMaintenance = VehicleMaintenancesHelper::createTestVehicleMaintenance();

        $response = $this->deleteJson(
            "/api/v1/vehicles/maintenances/{$vehicleMaintenance->uuid}",
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
