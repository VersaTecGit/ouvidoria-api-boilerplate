<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Http\Response;
use Modules\Auth\Models\Versa360ScopePermissionMap;
use Modules\Auth\Support\Permissions;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Auth\Helpers\Versa360Helper;
use Tests\Traits\RefreshDatabaseWithTenant;

class Versa360ApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_create_new_versa360_scope_permission_map(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_VERSA360_SCOPE_PERMISSION_MAP->value]);
        $scope_id = fake()->uuid();

        $response = $this->postJson(
            '/api/v1/versa360',
            [
                'scope_id' => $scope_id,
                'permissions' => [Permissions::LIST_USERS->value],
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'id', 'scope_id', 'permissions', 'created_at', 'updated_at',
            ]);

        $this->assertTrue(
            Versa360ScopePermissionMap::where('scope_id', $scope_id)
                ->whereJsonContains('permissions', [Permissions::LIST_USERS->value])
                ->exists()
        );
    }

    public function test_should_validate_create_new_versa360_scope_permission_map(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_VERSA360_SCOPE_PERMISSION_MAP->value]);

        $response = $this->postJson(
            '/api/v1/versa360',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['scope_id']);
    }

    public function test_should_return_versa360_scope_permission_map_by_uuid(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VERSA360_SCOPE_PERMISSION_MAP->value]);

        $versa360ScopePermissionMap = Versa360Helper::createTestVersa360ScopePermissionMap();

        $response = $this->getJson(
            "/api/v1/versa360/{$versa360ScopePermissionMap->scope_id}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id', 'scope_id', 'permissions', 'created_at', 'updated_at',
            ]);
    }

    public function test_should_return_404_when_not_exists_versa360_scope_permission_map(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_VERSA360_SCOPE_PERMISSION_MAP->value]);

        $response = $this->getJson(
            '/api/v1/versa360/00000000-0000-0000-0000-000000000000',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_update_versa360_scope_permission_map(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_VERSA360_SCOPE_PERMISSION_MAP->value]);

        $versa360ScopePermissionMap = Versa360Helper::createTestVersa360ScopePermissionMap();

        $response = $this->putJson(
            "/api/v1/versa360/{$versa360ScopePermissionMap->scope_id}",
            [
                'permissions' => [Permissions::LIST_USERS->value, Permissions::CREATE_USERS->value],
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'permissions' => [Permissions::LIST_USERS->value, Permissions::CREATE_USERS->value],
            ]);
    }

    public function test_should_delete_versa360_scope_permission_map(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::DELETE_VERSA360_SCOPE_PERMISSION_MAP->value]);

        $versa360ScopePermissionMap = Versa360Helper::createTestVersa360ScopePermissionMap();

        $response = $this->deleteJson(
            "/api/v1/versa360/{$versa360ScopePermissionMap->scope_id}",
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
