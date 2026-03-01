<?php

declare(strict_types=1);

namespace Tests\Feature\Transport;

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Modules\Transport\Models\VehicleDocument;
use Modules\Transport\Support\VehicleDocumentType;
use Modules\Transport\Support\VehicleIpvaStatus;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Transport\Helpers\VehicleDocumentsHelper;
use Tests\Feature\Transport\Helpers\VehiclesHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class VehicleDocumentsApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_a_list_of_vehicle_documents(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        foreach (range(1, 10) as $number) {
            VehicleDocumentsHelper::createTestVehicleDocument($vehicle);
        }

        $response = $this->getJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/documents',
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

    public function test_should_return_vehicle_documents_page_2(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        foreach (range(1, 30) as $number) {
            VehicleDocumentsHelper::createTestVehicleDocument($vehicle);
        }

        $response = $this->getJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/documents?page=2',
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

    public function test_should_return_vehicle_documents_with_total_per_page(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        foreach (range(1, 30) as $number) {
            VehicleDocumentsHelper::createTestVehicleDocument($vehicle);
        }

        $response = $this->getJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/documents?per_page=5',
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

    public function test_should_create_new_vehicle_document(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        $response = $this->postJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/documents',
            [
                'type' => VehicleDocumentType::IPVA->value,
                'attributes' => [
                    'year' => 2024,
                    'amount' => 1500,
                    'due_date' => '2024-12-31',
                    'status' => VehicleIpvaStatus::PAID->value,
                ],
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
                'attributes',
                'files',
                'created_at',
                'updated_at',
            ]);

        $this->assertTrue(
            VehicleDocument::where('type', VehicleDocumentType::IPVA->value)
                ->where('vehicle_id', $vehicle->id)
                ->exists()
        );
    }

    public function test_should_validate_create_new_vehicle_document(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLES->value]);
        $vehicle = VehiclesHelper::createTestVehicle();

        $response = $this->postJson(
            '/api/v1/vehicles/' . $vehicle->uuid . '/documents',
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
                'attributes',
            ]);
    }

    public function test_should_return_vehicle_documents_by_uuid(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);

        $vehicleDocument = VehicleDocumentsHelper::createTestVehicleDocument();

        $response = $this->getJson(
            "/api/v1/vehicles/documents/{$vehicleDocument->uuid}",
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
                'attributes',
                'files',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_should_return_404_when_not_exists_vehicle_documents(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VEHICLES->value]);

        $response = $this->getJson(
            '/api/v1/vehicles/documents/00000000-0000-0000-0000-000000000000',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_update_vehicle_document(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLES->value]);

        $vehicleDocument = VehicleDocumentsHelper::createTestVehicleDocument();

        $response = $this->putJson(
            "/api/v1/vehicles/documents/{$vehicleDocument->uuid}",
            [
                'attributes' => [
                    'year' => 2024,
                    'amount' => 1500,
                    'due_date' => '2024-12-31',
                    'status' => VehicleIpvaStatus::PAID->value,
                ],
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['attributes' => [
                'year' => 2024,
                'amount' => 1500,
                'due_date' => '2024-12-31',
                'status' => VehicleIpvaStatus::PAID->value,
            ]]);
    }

    public function test_should_delete_vehicle_document(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VEHICLES->value]);

        $vehicleDocument = VehicleDocumentsHelper::createTestVehicleDocument();

        $response = $this->deleteJson(
            "/api/v1/vehicles/documents/{$vehicleDocument->uuid}",
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
