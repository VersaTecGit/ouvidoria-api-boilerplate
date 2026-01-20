<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Http\Response;
use Modules\Auth\Models\Role;
use Modules\Auth\Support\Permissions;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Auth\Helpers\RolesAndPermissionsHelper;
use Tests\Feature\Auth\Helpers\UsersHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class RolesAndPermissionsApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_it_should_return_all_permissions(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_PERMISSIONS->value]);

        $response = $this->getJson(
            '/api/v1/permissions',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_it_should_return_all_roles(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_ROLES->value]);

        foreach (range(1, 8) as $number) {
            RolesAndPermissionsHelper::createTestRole();
        }

        $response = $this->getJson(
            '/api/v1/roles',
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

    public function test_should_return_roles_page_2(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_ROLES->value]);

        foreach (range(1, 28) as $number) {
            RolesAndPermissionsHelper::createTestRole();
        }

        $response = $this->getJson(
            '/api/v1/roles?page=2',
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

    public function test_should_return_roles_with_total_per_page(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_ROLES->value]);

        foreach (range(1, 28) as $number) {
            RolesAndPermissionsHelper::createTestRole();
        }

        $response = $this->getJson(
            '/api/v1/roles?per_page=5',
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

    public function test_should_return_roles_with_filter(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_ROLES->value]);

        foreach (range(1, 7) as $number) {
            RolesAndPermissionsHelper::createTestRole();
        }

        Role::create(RolesAndPermissionsHelper::dumbRoleData());

        $response = $this->getJson(
            '/api/v1/roles?search=foo-role',
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

    public function test_should_create_new_role(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_ROLES->value]);

        $response = $this->postJson(
            '/api/v1/roles',
            RolesAndPermissionsHelper::dumbRoleData(),
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'name', 'description',
            ]);
    }

    public function test_should_return_role_by_id(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_ROLES->value]);

        $role = RolesAndPermissionsHelper::createTestRole();

        $response = $this->getJson(
            "/api/v1/roles/{$role->name}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'name', 'description',
            ]);
    }

    public function test_should_return_500_when_not_exists_role(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_ROLES->value]);

        $response = $this->getJson(
            '/api/v1/roles/test',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function test_should_update_role(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_ROLES->value]);

        $role = RolesAndPermissionsHelper::createTestRole();

        $response = $this->putJson(
            "/api/v1/roles/{$role->name}",
            [
                'name' => 'foo-role-2',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['name' => 'foo-role-2']);
    }

    public function test_should_delete_role(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::DELETE_ROLES->value]);

        $role = RolesAndPermissionsHelper::createTestRole();

        $response = $this->deleteJson(
            "/api/v1/roles/{$role->name}",
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_should_return_all_members_from_role(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_ROLES->value]);
        $role = RolesAndPermissionsHelper::createTestRole();

        foreach (range(1, 8) as $number) {
            $user = UsersHelper::createTestUser();
            $user->assignRole($role);
        }

        $response = $this->getJson(
            "/api/v1/roles/{$role->name}/members",
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
        $this->assertCount(8, $responseData);

        $this->assertEquals(1, $responseMeta['current_page']);
        $this->assertEquals(1, $responseMeta['last_page']);
        $this->assertEquals(20, $responseMeta['per_page']);
        $this->assertEquals(8, $responseMeta['total']);
    }

    public function test_should_return_all_permissions_from_role(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_ROLE_PERMISSIONS->value]);
        $role = RolesAndPermissionsHelper::createTestRole();

        foreach (range(1, 8) as $number) {
            $permission = RolesAndPermissionsHelper::createTestPermission();
            $role->givePermissionTo($permission);
        }

        $response = $this->getJson(
            "/api/v1/roles/{$role->name}/permissions",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                '*' => ['name', 'description'],
            ]);
    }

    public function test_should_update_role_permissions(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_ROLE_PERMISSIONS->value]);
        $role = Role::findByName('test-role');

        $permissions = [];
        foreach (range(1, 8) as $number) {
            $permission = RolesAndPermissionsHelper::createTestPermission();
            $permissions[] = $permission->name;
        }

        $response = $this->postJson(
            "/api/v1/roles/{$role->name}/permissions",
            [
                'permissions' => $permissions,
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
