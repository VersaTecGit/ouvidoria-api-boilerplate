<?php

declare(strict_types=1);

namespace Tests\Feature\Logs;

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Modules\Common\Logs\Models\AccessLog;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Logs\Helpers\AccessLogsHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class AccessLogsApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_a_list_of_access_logs(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_ACCESS_LOGS->value]);

        foreach (range(1, 9) as $number) {
            AccessLogsHelper::createTestAccessLog();
        }

        $response = $this->getJson(
            '/api/v1/logs/access',
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

    public function test_should_return_access_logs_page_2(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_ACCESS_LOGS->value]);

        foreach (range(1, 29) as $number) {
            AccessLogsHelper::createTestAccessLog();
        }

        $response = $this->getJson(
            '/api/v1/logs/access?page=2',
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

    public function test_should_return_access_logs_with_total_per_page(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_ACCESS_LOGS->value]);

        foreach (range(1, 29) as $number) {
            AccessLogsHelper::createTestAccessLog();
        }

        $response = $this->getJson(
            '/api/v1/logs/access?per_page=5',
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

    public function test_should_return_access_logs_with_filter(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_ACCESS_LOGS->value]);

        foreach (range(1, 9) as $number) {
            AccessLogsHelper::createTestAccessLog();
        }

        AccessLog::create(AccessLogsHelper::dumbAccessLogData());

        $response = $this->getJson(
            '/api/v1/logs/access?search=fake-message',
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

    public function test_should_return_access_logs_with_date_range(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_ACCESS_LOGS->value]);

        foreach (range(1, 9) as $number) {
            AccessLogsHelper::createTestAccessLog();
        }

        AccessLog::create(AccessLogsHelper::dumbAccessLogData());

        $response = $this->getJson(
            '/api/v1/logs/access?start_date=2021-01-01&end_date=2021-01-01',
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
        $this->assertCount(0, $responseData);

        $this->assertEquals(1, $responseMeta['current_page']);
        $this->assertEquals(1, $responseMeta['last_page']);
        $this->assertEquals(20, $responseMeta['per_page']);
        $this->assertEquals(0, $responseMeta['total']);
    }
}
