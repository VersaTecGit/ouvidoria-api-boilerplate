<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Auth\Helpers\RolesAndPermissionsHelper;
use Tests\Feature\Auth\Helpers\UsersHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class ImpersonationTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_it_can_impersonate_another_user()
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::IMPERSONATE->value]);

        $role = RolesAndPermissionsHelper::createTestRole();
        $role->givePermissionTo(Permissions::BE_IMPERSONATED->value);
        $user = UsersHelper::createTestUser($role);

        $response = $this->postJson(
            "api/v1/auth/impersonate/take/{$user->uuid}",
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['type', 'token']);

        $impersonatedToken = $response->json('token');
        $response = $this->getJson(
            'api/v1/auth/me',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$impersonatedToken}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['id', 'name', 'email', 'login', 'roles', 'permissions']);
        $response->assertJsonFragment(['name' => $user->name]);
    }

    public function test_it_can_leave_impersonation()
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::IMPERSONATE->value]);

        $role = RolesAndPermissionsHelper::createTestRole();
        $role->givePermissionTo(Permissions::BE_IMPERSONATED->value);
        $user = UsersHelper::createTestUser($role);

        $response = $this->postJson(
            "api/v1/auth/impersonate/take/{$user->uuid}",
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['type', 'token']);

        $impersonatedToken = $response->json('token');
        $response = $this->deleteJson(
            'api/v1/auth/impersonate/leave',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$impersonatedToken}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['type', 'token']);
    }

    public function test_should_return_impersonation_info(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::IMPERSONATE->value]);

        $role = RolesAndPermissionsHelper::createTestRole();
        $role->givePermissionTo(Permissions::BE_IMPERSONATED->value);
        $role->givePermissionTo(Permissions::IMPERSONATE->value);
        $user = UsersHelper::createTestUser($role);

        $response = $this->postJson(
            "api/v1/auth/impersonate/take/{$user->uuid}",
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['type', 'token']);

        $impersonatedToken = $response->json('token');
        $response = $this->getJson(
            'api/v1/auth/impersonate/info',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$impersonatedToken}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['type', 'token', 'is_impersonating', 'impersonator', 'impersonated']);
    }
}
