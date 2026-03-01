<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Http\Response;
use Modules\Auth\Models\Permission;
use Modules\Auth\Models\Role;
use Modules\Auth\Models\User;
use PragmaRX\Google2FA\Google2FA;
use Tests\Feature\Auth\Helpers\UsersHelper;

abstract class AuthenticatedTestCase extends TestCase
{
    protected function loginAndGetToken(?Role $role = null, ?User $user = null): string
    {
        if (! $user) {
            $user = UsersHelper::createTestUser($role);
        } else {
            if ($role) {
                $user->assignRole($role);
            }
        }

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

        return $response->json('token');
    }

    protected function loginWith2faAndGetToken(?User $user = null): string
    {
        if (! $user) {
            $user = UsersHelper::createTestUser();
        }

        if (! $user->two_factor_secret) {
            $google2fa = app(Google2FA::class);
            $user->forceFill([
                'two_factor_secret' => encrypt($google2fa->generateSecretKey()),
                'two_factor_confirmed_at' => now(),
            ])->save();
        }

        $response = $this->postJson(
            'api/v1/auth/login',
            [
                'login' => $user->login,
                'password' => 'password',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_OK);

        if ($response->json('token')) {
            return $response->json('token');
        }

        $uuid = $response->json('uuid');

        $google2fa = app(Google2FA::class);
        $secret = decrypt($user->two_factor_secret);
        $validCode = $google2fa->getCurrentOtp($secret);

        $response = $this->postJson(
            'api/v1/auth/login/2fa',
            [
                'uuid' => $uuid,
                'code' => $validCode,
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['type', 'token']);

        return $response->json('token');
    }

    protected function loginAndGetTokenWithPermissions(array $permissions, ?User $user = null): string
    {
        $role = new Role([
            'name' => 'test-role',
            'description' => 'Test Role',
        ]);
        $role->save();

        foreach ($permissions as $permission) {
            $role->givePermissionTo(Permission::findByName($permission));
        }

        return $this->loginAndGetToken($role, $user);
    }
}
