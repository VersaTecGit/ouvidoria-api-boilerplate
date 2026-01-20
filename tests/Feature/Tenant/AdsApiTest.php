<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Modules\Tenant\Models\Ads;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Tenant\Helpers\AdsHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class AdsApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_a_list_of_ads(): void
    {
        $token = $this->loginAndGetToken();

        foreach (range(1, 10) as $number) {
            AdsHelper::createTestAds();
        }

        $response = $this->getJson(
            '/api/v1/tenant/ads',
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

    public function test_should_return_ads_page_2(): void
    {
        $token = $this->loginAndGetToken();

        foreach (range(1, 30) as $number) {
            AdsHelper::createTestAds();
        }

        $response = $this->getJson(
            '/api/v1/tenant/ads?page=2',
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

    public function test_should_return_ads_with_total_per_page(): void
    {
        $token = $this->loginAndGetToken();

        foreach (range(1, 30) as $number) {
            AdsHelper::createTestAds();
        }

        $response = $this->getJson(
            '/api/v1/tenant/ads?per_page=5',
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

    public function test_should_return_ads_with_filter(): void
    {
        $token = $this->loginAndGetToken();

        foreach (range(1, 9) as $number) {
            AdsHelper::createTestAds();
        }

        Ads::create(AdsHelper::dumbAdsData());

        $response = $this->getJson(
            '/api/v1/tenant/ads?search=foo-title',
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

    public function test_should_create_new_ads(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_ADS_ITEMS->value]);

        $response = $this->postJson(
            '/api/v1/tenant/ads',
            AdsHelper::dumbAdsData(),
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'id', 'title', 'description', 'background_image_url', 'button_text', 'button_url', 'start_date', 'end_date', 'order', 'active', 'created_at', 'updated_at',
            ]);
    }

    public function test_should_reorder_ads(): void
    {
        $token = $this->loginAndGetToken();

        $ids = [];
        foreach (range(1, 10) as $number) {
            $ads = AdsHelper::createTestAds();
            $ids[] = $ads->uuid;
        }

        $response = $this->postJson(
            '/api/v1/tenant/ads/reorder',
            [
                'ids' => $ids,
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_should_validate_create_new_ads(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_ADS_ITEMS->value]);

        $response = $this->postJson(
            '/api/v1/tenant/ads',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_should_return_ads_by_uuid(): void
    {
        $token = $this->loginAndGetToken();

        $ads = AdsHelper::createTestAds();

        $response = $this->getJson(
            "/api/v1/tenant/ads/{$ads->uuid}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id', 'title', 'description', 'background_image_url', 'button_text', 'button_url', 'start_date', 'end_date', 'order', 'active', 'created_at', 'updated_at',
            ]);
    }

    public function test_should_return_404_when_not_exists_ads(): void
    {
        $token = $this->loginAndGetToken();

        $response = $this->getJson(
            '/api/v1/tenant/ads/00000000-0000-0000-0000-000000000000',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_update_ads(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_ADS_ITEMS->value]);

        $ads = AdsHelper::createTestAds();

        $response = $this->putJson(
            "/api/v1/tenant/ads/{$ads->uuid}",
            [
                'title' => 'New ADS 2',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['title' => 'New ADS 2']);
    }

    public function test_should_delete_ads(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::DELETE_ADS_ITEMS->value]);

        $ads = AdsHelper::createTestAds();

        $response = $this->deleteJson(
            "/api/v1/tenant/ads/{$ads->uuid}",
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
