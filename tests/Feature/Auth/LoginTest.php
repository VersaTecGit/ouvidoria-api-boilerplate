<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Http\Response;
use PragmaRX\Google2FA\Google2FA;
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

    public function test_should_login_with_email_successfully(): void
    {
        $user = UsersHelper::createTestUser();
        $params = [
            'login' => $user->email,
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

    public function test_should_return_two_factor_required_when_enabled(): void
    {
        $user = UsersHelper::createTestUser();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();

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
        $response->assertJsonFragment([
            'two_factor_required' => true,
            'uuid' => $user->uuid,
        ]);
        $response->assertJsonMissing(['token']);
    }

    public function test_should_login_with_valid_two_factor_code(): void
    {
        $user = UsersHelper::createTestUser();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $validCode = $google2fa->getCurrentOtp($secret);

        $params = [
            'uuid' => $user->uuid,
            'code' => $validCode,
        ];

        $response = $this->postJson(
            'api/v1/auth/login/2fa',
            $params,
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['type', 'token']);
    }

    public function test_should_fail_login_with_invalid_two_factor_code(): void
    {
        $user = UsersHelper::createTestUser();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $params = [
            'uuid' => $user->uuid,
            'code' => '000000',
        ];

        $response = $this->postJson(
            'api/v1/auth/login/2fa',
            $params,
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_should_login_with_recovery_code(): void
    {
        $user = UsersHelper::createTestUser();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();
        $recoveryCode = 'recovery-code-1';

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode([$recoveryCode])),
        ])->save();

        $params = [
            'uuid' => $user->uuid,
            'code' => $recoveryCode,
        ];

        $response = $this->postJson(
            'api/v1/auth/login/2fa',
            $params,
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['type', 'token']);

        $user->refresh();
        $remainingCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        $this->assertNotContains($recoveryCode, $remainingCodes);
    }
}
