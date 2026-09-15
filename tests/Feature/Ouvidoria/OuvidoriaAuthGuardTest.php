<?php

declare(strict_types=1);

namespace Tests\Feature\Ouvidoria;

use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AuthenticatedTestCase;
use Tests\Traits\RefreshDatabaseWithTenant;

/**
 * The other half of the public/private split. The public routes are proven
 * open in PublicManifestationsApiTest; this file proves every management
 * route is closed twice over: no token means 401, a token without the
 * permission means 403. The target record never has to exist — both
 * gates sit in front of the controller.
 */
final class OuvidoriaAuthGuardTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    private const array HEADERS = [
        'X-Domain' => 'foo',
        'Accept' => 'application/json',
    ];

    private const string ANY_UUID = '00000000-0000-4000-8000-000000000000';

    /**
     * @return array<string, array{string, string}>
     */
    public static function managementRoutes(): array
    {
        $uuid = self::ANY_UUID;

        return [
            'list unit types' => ['GET', '/api/v1/unit-types'],

            'list units' => ['GET', '/api/v1/units'],
            'create unit' => ['POST', '/api/v1/units'],
            'show unit' => ['GET', "/api/v1/units/{$uuid}"],
            'update unit' => ['PUT', "/api/v1/units/{$uuid}"],
            'delete unit' => ['DELETE', "/api/v1/units/{$uuid}"],

            'list destination agencies' => ['GET', '/api/v1/destination-agencies'],
            'create destination agency' => ['POST', '/api/v1/destination-agencies'],
            'show destination agency' => ['GET', "/api/v1/destination-agencies/{$uuid}"],
            'update destination agency' => ['PUT', "/api/v1/destination-agencies/{$uuid}"],
            'delete destination agency' => ['DELETE', "/api/v1/destination-agencies/{$uuid}"],

            'list manifestations' => ['GET', '/api/v1/manifestations'],
            'create manifestation' => ['POST', '/api/v1/manifestations'],
            'show manifestation' => ['GET', "/api/v1/manifestations/{$uuid}"],
            'update manifestation' => ['PUT', "/api/v1/manifestations/{$uuid}"],
            'delete manifestation' => ['DELETE', "/api/v1/manifestations/{$uuid}"],
            'respond manifestation' => ['POST', "/api/v1/manifestations/{$uuid}/respond"],
            'log manifestation' => ['POST', "/api/v1/manifestations/{$uuid}/logs"],
        ];
    }

    #[DataProvider('managementRoutes')]
    public function test_management_route_rejects_anonymous_caller(string $method, string $uri): void
    {
        $response = $this->json($method, $uri, [], self::HEADERS);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    #[DataProvider('managementRoutes')]
    public function test_management_route_rejects_token_without_permission(string $method, string $uri): void
    {
        // No role, no permissions: authenticates, then the `can` gate must refuse.
        $token = $this->loginAndGetToken();

        $response = $this->json($method, $uri, [], [
            ...self::HEADERS,
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_public_routes_stay_open_to_the_same_anonymous_caller(): void
    {
        // Sanity check that the guard is per-route, not a blanket on the module.
        $this->getJson('/api/v1/public/units', self::HEADERS)->assertStatus(Response::HTTP_OK);
        $this->getJson('/api/v1/public/destination-agencies', self::HEADERS)->assertStatus(Response::HTTP_OK);
    }
}
