<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Tests\AuthenticatedTestCase;
use Tests\Traits\RefreshDatabaseWithTenant;

class GetMeTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_me_successfully(): void
    {
        $token = $this->loginAndGetToken();

        $response = $this->getJson(
            'api/v1/auth/me',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['id', 'name', 'email', 'login', 'roles', 'permissions']);
    }

    public function test_should_return_me_with_roles_and_permissions(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([
            Permissions::LIST_USERS->value, Permissions::CREATE_USERS->value,
        ]);

        $response = $this->getJson(
            'api/v1/auth/me',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['id', 'name', 'email', 'login', 'roles', 'permissions']);
        $response->assertJsonFragment([
            'permissions' => [Permissions::LIST_USERS->value, Permissions::CREATE_USERS->value],
        ]);
    }

    public function test_should_not_return_me_with_invalid_token(): void
    {
        $response = $this->getJson(
            'api/v1/auth/me',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer invalid-token',
            ]
        );

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonStructure(['message']);
    }
}
