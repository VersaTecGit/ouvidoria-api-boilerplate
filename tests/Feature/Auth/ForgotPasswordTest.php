<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Http\Response;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Auth\Helpers\AuthHelper;
use Tests\Feature\Auth\Helpers\UsersHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class ForgotPasswordTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_send_forgot_password_email(): void
    {
        $user = UsersHelper::createTestUser();
        $response = $this->postJson(
            'api/v1/auth/forgot-password',
            [
                'login' => $user->login,
                'callback_url' => 'https://example.com/reset-password',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['message']);
    }

    public function test_should_not_send_forgot_password_email_with_invalid_login(): void
    {
        $response = $this->postJson(
            'api/v1/auth/forgot-password',
            [
                'login' => 'invalid-login',
                'callback_url' => 'https://example.com/reset-password',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(['errors']);
    }

    public function test_should_not_send_forgot_password_email_with_invalid_callback_url(): void
    {
        $user = UsersHelper::createTestUser();
        $response = $this->postJson(
            'api/v1/auth/forgot-password',
            [
                'login' => $user->login,
                'callback_url' => 'invalid-url',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(['errors']);
    }

    public function test_should_create_new_password(): void
    {
        $user = UsersHelper::createTestUser();
        $token = AuthHelper::createTestResetPasswordToken($user);

        $response = $this->postJson(
            'api/v1/auth/reset-password',
            [
                'token' => $token,
                'login' => $user->login,
                'password' => 'My-new-secret-password-1234',
                'password_confirmation' => 'My-new-secret-password-1234',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['message']);
    }
}
