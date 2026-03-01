<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Http\Response;
use PragmaRX\Google2FA\Google2FA;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Auth\Helpers\UsersHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class TwoFactorTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_enable_two_factor_authentication(): void
    {
        $user = UsersHelper::createTestUser();
        $token = $this->loginAndGetToken(null, $user);

        $response = $this->postJson(
            '/api/v1/auth/2fa/enable',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'secret',
            'qr_code_url',
            'recovery_codes',
        ]);

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_should_confirm_two_factor_authentication(): void
    {
        $user = UsersHelper::createTestUser();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
        ])->save();

        $token = $this->loginAndGetToken(null, $user);
        $validCode = $google2fa->getCurrentOtp($secret);

        $response = $this->postJson(
            '/api/v1/auth/2fa/confirm',
            ['code' => $validCode],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);
    }

    public function test_should_fail_to_confirm_with_invalid_code(): void
    {
        $user = UsersHelper::createTestUser();
        $google2fa = app(Google2FA::class);

        $user->forceFill([
            'two_factor_secret' => encrypt($google2fa->generateSecretKey()),
        ])->save();

        $token = $this->loginAndGetToken(null, $user);

        $response = $this->postJson(
            '/api/v1/auth/2fa/confirm',
            ['code' => '000000'],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $user->refresh();
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_should_disable_two_factor_authentication(): void
    {
        $user = UsersHelper::createTestUser();
        $google2fa = app(Google2FA::class);

        $user->forceFill([
            'two_factor_secret' => encrypt($google2fa->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode([])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $token = $this->loginWith2faAndGetToken($user);

        $response = $this->deleteJson(
            '/api/v1/auth/2fa/disable',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_should_regenerate_recovery_codes(): void
    {
        $user = UsersHelper::createTestUser();
        $google2fa = app(Google2FA::class);

        $user->forceFill([
            'two_factor_secret' => encrypt($google2fa->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(['old-code'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $token = $this->loginWith2faAndGetToken($user);

        $response = $this->postJson(
            '/api/v1/auth/2fa/regenerate',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure(['recovery_codes']);

        $newCodes = $response->json('recovery_codes');
        $this->assertCount(8, $newCodes);
        $this->assertNotContains('old-code', $newCodes);
    }
}
