<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Tests\AuthenticatedTestCase;
use Tests\Traits\RefreshDatabaseWithTenant;

class AuthSettingsTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_all_auth_settings(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_AUTH_SETTINGS->value]);

        $response = $this->getJson(
            'api/v1/auth/settings',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['auth']);
    }

    public function test_should_update_auth_settings(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_AUTH_SETTINGS->value]);

        $response = $this->putJson(
            'api/v1/auth/settings',
            [
                'auth' => [
                    'redirect_on_first_login' => false,
                    'redirect_on_first_login_path' => '/home',
                    'force_change_password_on_first_login' => false,
                ],
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['auth'])
            ->assertJsonFragment(
                [
                    'auth' => [
                        'redirect_on_first_login' => false,
                        'redirect_on_first_login_path' => '/home',
                        'force_change_password_on_first_login' => false,
                    ],
                ]
            );
    }
}
