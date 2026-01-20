<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Http\Response;
use Tests\AuthenticatedTestCase;
use Tests\Traits\RefreshDatabaseWithTenant;

class LogoutTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_logout_successfully(): void
    {
        $token = $this->loginAndGetToken();

        $response = $this->postJson(
            'api/v1/auth/logout',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $response->assertNoContent();
    }

    public function test_should_not_logout_with_invalid_token(): void
    {
        $response = $this->postJson(
            'api/v1/auth/logout',
            [],
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
