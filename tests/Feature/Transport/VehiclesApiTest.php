<?php

declare(strict_types=1);

namespace Tests\Feature\Transport;

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Modules\Transport\Models\Vehicle;
use Modules\Transport\Support\LicenseCategory;
use Modules\Transport\Support\VehicleFuel;
use Modules\Transport\Support\VehicleStatus;
use Modules\Transport\Support\VehicleType;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Transport\Helpers\VehiclesHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class VehiclesApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_a_list_of_vehicles(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_VEHICLES->value]);

        foreach (range(1, 10) as $number) {
            VehiclesHelper::createTestVehicle();
        }

        $response = $this->getJson(
            '/api/v1/vehicles',
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

    public function test_should_return_vehicles_page_2(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_VEHICLES->value]);

        foreach (range(1, 30) as $number) {
            VehiclesHelper::createTestVehicle();
        }

        $response = $this->getJson(
            '/api/v1/vehicles?page=2',
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

    public function test_should_return_vehicles_with_total_per_page(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_VEHICLES->value]);

        foreach (range(1, 30) as $number) {
            VehiclesHelper::createTestVehicle();
        }

        $response = $this->getJson(
            '/api/v1/vehicles?per_page=5',
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

    public function test_should_return_vehicles_with_filter(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_VEHICLES->value]);

        foreach (range(1, 30) as $number) {
            VehiclesHelper::createTestVehicle();
        }

        Vehicle::create(VehiclesHelper::dumbVehicleData());

        $response = $this->getJson(
            '/api/v1/vehicles?search=ABC1234',
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

    public function test_should_create_new_vehicle(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_VEHICLES->value]);

        $response = $this->postJson(
            '/api/v1/vehicles',
            [
                'status' => VehicleStatus::ACTIVE->value,
                'required_license_categories' => [LicenseCategory::B->value],
                'plate' => 'ABC1234',
                'model' => 'Model X',
                'brand' => 'Brand Y',
                'capacity' => 5,
                'color' => 'Red',
                'fuels' => [VehicleFuel::DIESEL->value, VehicleFuel::GASOLINE->value],
                'manufacture_year' => '2020',
                'renavam' => '123456789',
                'chassis_number' => '1HGBH41JXMN109186',
                'type' => VehicleType::CAR->value,
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
                'required_license_categories',
                'plate',
                'model',
                'brand',
                'capacity',
                'color',
                'fuels',
                'manufacture_year',
                'renavam',
                'chassis_number',
                'type',
                'other_type',
                'status',
                'pictures',
                'created_at',
                'updated_at',
            ]);

        $this->assertTrue(
            Vehicle::where('plate', 'ABC1234')
                ->where('renavam', '123456789')
                ->where('chassis_number', '1HGBH41JXMN109186')
                ->exists()
        );
    }

    public function test_should_validate_create_new_vehicle(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_VEHICLES->value]);

        $response = $this->postJson(
            '/api/v1/vehicles',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'required_license_categories',
                'plate',
                'model',
                'brand',
                'capacity',
                'fuels',
                'manufacture_year',
                'renavam',
                'chassis_number',
                'type',
            ]);
    }

    public function test_should_return_vehicles_by_uuid(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);

        $vehicle = VehiclesHelper::createTestVehicle();

        $response = $this->getJson(
            "/api/v1/vehicles/{$vehicle->uuid}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'required_license_categories',
                'plate',
                'model',
                'brand',
                'capacity',
                'color',
                'fuels',
                'manufacture_year',
                'renavam',
                'chassis_number',
                'type',
                'other_type',
                'status',
                'pictures',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_should_return_404_when_not_exists_vehicles(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);

        $response = $this->getJson(
            '/api/v1/vehicles/00000000-0000-0000-0000-000000000000',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_update_vehicle(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLES->value]);

        $vehicle = VehiclesHelper::createTestVehicle();

        $response = $this->putJson(
            "/api/v1/vehicles/{$vehicle->uuid}",
            [
                'plate' => 'ABC1235',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['plate' => 'ABC1235']);
    }

    public function test_should_delete_vehicle(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::DELETE_VEHICLES->value]);

        $vehicle = VehiclesHelper::createTestVehicle();

        $response = $this->deleteJson(
            "/api/v1/vehicles/{$vehicle->uuid}",
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
