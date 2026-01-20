<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Http\Response;
use Tests\Feature\Auth\Helpers\UsersHelper;
use Tests\TestCase;
use Tests\Traits\RefreshDatabaseWithTenant;

class LoginTest extends TestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_login_successfully(): void
    {
        $user = UsersHelper::createTestUser();
        $params = [
            'login' => $user->login,
            'password' => 'password',
        ];

        $response = $this->postJson(
            'api/v1/auth/login',
            $params,
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['type', 'token']);
    }

    public function test_should_fail_to_login_with_wrong_credentials(): void
    {
        $user = UsersHelper::createTestUser();
        $params = [
            'login' => $user->login,
            'password' => 'test',
        ];

        $response = $this->postJson(
            'api/v1/auth/login',
            $params,
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_should_fail_to_login_with_wrong_domain(): void
    {
        $user = UsersHelper::createTestUser();
        $params = [
            'login' => $user->login,
            'password' => 'password',
        ];

        $response = $this->postJson(
            'api/v1/auth/login',
            $params,
            [
                'X-Domain' => 'bar',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function test_should_refresh_token_to_authenticated_user(): void
    {
        $user = UsersHelper::createTestUser();
        $params = [
            'login' => $user->login,
            'password' => 'password',
        ];

        $response = $this->postJson(
            'api/v1/auth/login',
            $params,
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['type', 'token']);

        $token = $response->json('token');

        $response = $this->postJson(
            'api/v1/auth/refresh',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['type', 'token']);
    }

    public function test_should_fail_refresh_when_user_not_authenticated(): void
    {
        $response = $this->postJson(
            'api/v1/auth/refresh',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
