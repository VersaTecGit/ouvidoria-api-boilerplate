<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Http\Response;
use Modules\Auth\Models\Role;
use Modules\Auth\Models\User;
use Modules\Auth\Support\DefaultRoles;
use Modules\Auth\Support\Permissions;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Auth\Helpers\RolesAndPermissionsHelper;
use Tests\Feature\Auth\Helpers\UsersHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class UsersApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_a_list_of_users(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_USERS->value]);

        foreach (range(1, 8) as $number) {
            UsersHelper::createTestUser();
        }

        $response = $this->getJson(
            '/api/v1/users',
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

    public function test_should_return_users_page_2(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_USERS->value]);

        foreach (range(1, 28) as $number) {
            UsersHelper::createTestUser();
        }

        $response = $this->getJson(
            '/api/v1/users?page=2',
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

    public function test_should_return_users_with_total_per_page(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_USERS->value]);

        foreach (range(1, 28) as $number) {
            UsersHelper::createTestUser();
        }

        $response = $this->getJson(
            '/api/v1/users?per_page=5',
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

    public function test_should_return_users_with_filter(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_USERS->value]);

        foreach (range(1, 6) as $number) {
            UsersHelper::createTestUser();
        }

        user::create(UsersHelper::dumbuserData());

        $response = $this->getJson(
            '/api/v1/users?search=john.doe',
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

    public function test_should_create_new_user(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_USERS->value]);

        $role = RolesAndPermissionsHelper::createTestRole();

        $response = $this->postJson(
            '/api/v1/users',
            [
                'name' => 'John Doe',
                'login' => 'john-doe',
                'email' => 'john@test.com',
                'role' => $role->id,
                'extra_permissions' => [Permissions::LIST_USERS->value],
                'password' => 's3CR3t@!',
                'password_confirmation' => 's3CR3t@!',
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
                'name',
                'login',
                'email',
                'roles',
                'permissions',
                'driver',
                'created_at',
                'updated_at',
            ]);

        $this->assertTrue(
            user::where('login', 'john-doe')->first()->hasPermissionTo(
                Permissions::LIST_USERS->value
            )
        );
    }

    public function test_should_validate_create_new_user(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_USERS->value]);

        $response = $this->postJson(
            '/api/v1/users',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name', 'login', 'email', 'password']);
    }

    public function test_should_return_user_by_uuid(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_USERS->value]);

        $user = UsersHelper::createTestUser();

        $response = $this->getJson(
            "/api/v1/users/{$user->uuid}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'name',
                'login',
                'email',
                'roles',
                'permissions',
                'driver',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_should_return_404_when_not_exists_user(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_USERS->value]);

        $response = $this->getJson(
            '/api/v1/users/00000000-0000-0000-0000-000000000000',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_update_user(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_USERS->value]);

        $user = UsersHelper::createTestUser();

        $response = $this->putJson(
            "/api/v1/users/{$user->uuid}",
            [
                'name' => 'John Doe 2',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['name' => 'John Doe 2']);
    }

    public function test_should_delete_user(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::DELETE_USERS->value]);

        $user = UsersHelper::createTestUser();

        $response = $this->deleteJson(
            "/api/v1/users/{$user->uuid}",
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_should_return_a_list_of_the_user_roles(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_USER_ROLES->value]);

        $user = UsersHelper::createTestUser();
        $role = RolesAndPermissionsHelper::createTestRole();

        $user->assignRole($role);

        $response = $this->getJson(
            "/api/v1/users/{$user->uuid}/roles",
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

    public function test_should_return_a_empty_list_of_the_user_roles(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_USER_ROLES->value]);

        $user = UsersHelper::createTestUser();

        $response = $this->getJson(
            "/api/v1/users/{$user->uuid}/roles",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([]);
    }

    public function test_should_update_user_roles(): void
    {
        $token = $this->loginAndGetToken(Role::where('name', DefaultRoles::ADMIN->value)->first());

        $user = UsersHelper::createTestUser();
        $user->givePermissionTo([Permissions::LIST_USERS->value, Permissions::VIEW_USERS->value]);

        $role = RolesAndPermissionsHelper::createTestRole();

        $response = $this->post(
            "/api/v1/users/{$user->uuid}/roles",
            [
                'roles' => [$role->id],
                'extra_permissions' => [Permissions::LIST_USERS->value, Permissions::EDIT_USERS->value],
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertTrue($user->hasRole($role));
        $this->assertTrue($user->hasPermissionTo(Permissions::LIST_USERS->value));
        $this->assertTrue($user->hasPermissionTo(Permissions::EDIT_USERS->value));
        $this->assertFalse($user->hasPermissionTo(Permissions::VIEW_USERS->value));
    }

    public function test_should_change_user_password(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_USERS_PASSWORDS->value]);

        $user = UsersHelper::createTestUser();

        $response = $this->putJson(
            "/api/v1/users/{$user->uuid}/password",
            [
                'password' => 's3CR3t@!',
                'password_confirmation' => 's3CR3t@!',
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
