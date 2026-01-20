<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Modules\Tenant\Models\Theme;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Tenant\Helpers\ThemesHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class ThemesApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_a_list_of_themes(): void
    {
        $token = $this->loginAndGetToken();

        foreach (range(1, 9) as $number) {
            ThemesHelper::createTestTheme();
        }

        $response = $this->getJson(
            '/api/v1/tenant/themes',
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

    public function test_should_return_themes_page_2(): void
    {
        $token = $this->loginAndGetToken();

        foreach (range(1, 29) as $number) {
            ThemesHelper::createTestTheme();
        }

        $response = $this->getJson(
            '/api/v1/tenant/themes?page=2',
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

    public function test_should_return_themes_with_total_per_page(): void
    {
        $token = $this->loginAndGetToken();

        foreach (range(1, 29) as $number) {
            ThemesHelper::createTestTheme();
        }

        $response = $this->getJson(
            '/api/v1/tenant/themes?per_page=5',
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

    public function test_should_return_themes_with_filter(): void
    {
        $token = $this->loginAndGetToken();

        foreach (range(1, 9) as $number) {
            ThemesHelper::createTestTheme();
        }

        Theme::create(ThemesHelper::dumbThemeData());

        $response = $this->getJson(
            '/api/v1/tenant/themes?search=foo-title',
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

    // Implement the test_should_create_new_themes method when s3 bucket is configured

    // Implement the test_should_validate_create_new_themes method when s3 bucket is configured

    public function test_should_return_themes_by_uuid(): void
    {
        $token = $this->loginAndGetToken();

        $theme = ThemesHelper::createTestTheme();

        $response = $this->getJson(
            "/api/v1/tenant/themes/{$theme->uuid}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'title',
                'institutional_website_url',
                'primary_color_light',
                'secondary_color_light',
                'primary_color_dark',
                'secondary_color_dark',
                'app_store_url',
                'google_play_url',
                'active',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_should_return_404_when_not_exists_themes(): void
    {
        $token = $this->loginAndGetToken();

        $response = $this->getJson(
            '/api/v1/tenant/themes/00000000-0000-0000-0000-000000000000',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_update_themes(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_THEMES_ITEMS->value]);

        $theme = ThemesHelper::createTestTheme();

        $response = $this->putJson(
            "/api/v1/tenant/themes/{$theme->uuid}",
            [
                'title' => 'New THEME 2',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['title' => 'New THEME 2']);
    }

    public function test_should_delete_themes(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::DELETE_THEMES_ITEMS->value]);

        $theme = ThemesHelper::createTestTheme();

        $theme->update(['active' => false]);

        $response = $this->deleteJson(
            "/api/v1/tenant/themes/{$theme->uuid}",
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
