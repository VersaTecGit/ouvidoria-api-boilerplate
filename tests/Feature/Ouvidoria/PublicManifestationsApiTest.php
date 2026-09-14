<?php

declare(strict_types=1);

namespace Tests\Feature\Ouvidoria;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Ouvidoria\Models\Manifestation;
use Modules\Ouvidoria\Support\ManifestationStatus;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Ouvidoria\Helpers\DestinationAgenciesHelper;
use Tests\Feature\Ouvidoria\Helpers\ManifestationsHelper;
use Tests\Feature\Ouvidoria\Helpers\UnitsHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

/**
 * The anonymous citizen surface. Two things are under test here: that a
 * manifestation can be filed and read back with no token at all, and that
 * the payload served to that anonymous caller never carries more than
 * protocol, type, status, date and the public timeline.
 */
class PublicManifestationsApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    private const string CENTRAL_BUCKET = 'central-test-bucket';

    private const array PUBLIC_HEADERS = [
        'X-Domain' => 'foo',
        'Accept' => 'application/json',
    ];

    private const array PUBLIC_KEYS = ['protocol_number', 'type', 'status', 'created_at', 'logs'];

    private const array PUBLIC_LOG_KEYS = ['content', 'status', 'created_at'];

    private const array FORBIDDEN_KEYS = [
        'id', 'uuid', 'subject', 'description', 'occurrence_place', 'destination_agency',
        'destination_agency_id', 'unit', 'unit_id', 'is_anonymous', 'manifestant',
        'manifestant_name', 'manifestant_email', 'manifestant_phone', 'manifestant_document',
        'manifestant_address', 'attachments', 'parecer', 'responded_by', 'responded_by_id',
        'responded_at', 'user_id', 'updated_at',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.central.bucket' => self::CENTRAL_BUCKET]);
    }

    public function test_anonymous_citizen_can_create_manifestation_without_token(): void
    {
        $agency = DestinationAgenciesHelper::createTestDestinationAgency();
        $unit = UnitsHelper::createTestUnit();

        // Manifestant data travels in the payload on purpose: anonymity must not depend on the form.
        $payload = ManifestationsHelper::dumbPublicManifestationData($agency->uuid, [
            'is_anonymous' => true,
            'unit_id' => $unit->uuid,
        ]);

        $response = $this->postJson('/api/v1/public/manifestations', $payload, self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_CREATED);

        $protocol = $response->json('protocol_number');
        $this->assertMatchesRegularExpression('/^OUV-\d{4}-[A-F0-9]{8}$/', $protocol);
        $this->assertSame(ManifestationStatus::RECEIVED->value, $response->json('status'));

        $this->assertDatabaseHas('manifestations', [
            'protocol_number' => $protocol,
            'destination_agency_id' => $agency->id,
            'unit_id' => $unit->id,
            'is_anonymous' => true,
            'manifestant_name' => null,
            'manifestant_email' => null,
            'manifestant_phone' => null,
            'manifestant_document' => null,
            'manifestant_address' => null,
        ]);
    }

    public function test_identified_citizen_create_persists_manifestant_columns(): void
    {
        $agency = DestinationAgenciesHelper::createTestDestinationAgency();

        $payload = ManifestationsHelper::dumbPublicManifestationData($agency->uuid);

        $response = $this->postJson('/api/v1/public/manifestations', $payload, self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('manifestations', [
            'protocol_number' => $response->json('protocol_number'),
            'is_anonymous' => false,
            'manifestant_name' => 'Maria da Silva',
            'manifestant_email' => 'maria@example.com',
            'manifestant_phone' => '(11) 99999-0000',
            'manifestant_document' => '529.982.247-25',
        ]);
    }

    public function test_identified_create_requires_name_email_and_phone(): void
    {
        $agency = DestinationAgenciesHelper::createTestDestinationAgency();

        $payload = ManifestationsHelper::dumbPublicManifestationData($agency->uuid);
        unset($payload['manifestant_name'], $payload['manifestant_email'], $payload['manifestant_phone']);

        $response = $this->postJson('/api/v1/public/manifestations', $payload, self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['manifestant_name', 'manifestant_email', 'manifestant_phone']);

        $this->assertSame(0, Manifestation::query()->count());
    }

    public function test_create_without_is_anonymous_flag_is_rejected(): void
    {
        $agency = DestinationAgenciesHelper::createTestDestinationAgency();

        // A caller reaching the endpoint outside the form omits the flag entirely.
        // It must not slip through defaulting to identified-with-empty-contact.
        $payload = ManifestationsHelper::dumbPublicManifestationData($agency->uuid);
        unset(
            $payload['is_anonymous'],
            $payload['manifestant_name'],
            $payload['manifestant_email'],
            $payload['manifestant_phone'],
        );

        $response = $this->postJson('/api/v1/public/manifestations', $payload, self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['is_anonymous']);

        $this->assertSame(0, Manifestation::query()->count());
    }

    public function test_attachment_key_outside_public_prefix_is_rejected(): void
    {
        $agency = DestinationAgenciesHelper::createTestDestinationAgency();
        $uuid = (string) Str::uuid();

        $payload = ManifestationsHelper::dumbPublicManifestationData($agency->uuid, [
            'attachments' => [$this->attachment($uuid, key: "avatars/{$uuid}.pdf")],
        ]);

        $response = $this->postJson('/api/v1/public/manifestations', $payload, self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['attachments.0.key']);

        $this->assertSame(0, Manifestation::query()->count());
    }

    public function test_attachment_uuid_mismatching_key_is_rejected(): void
    {
        $agency = DestinationAgenciesHelper::createTestDestinationAgency();
        $keyUuid = (string) Str::uuid();

        $payload = ManifestationsHelper::dumbPublicManifestationData($agency->uuid, [
            'attachments' => [$this->attachment((string) Str::uuid(), key: "public-manifestations/{$keyUuid}.pdf")],
        ]);

        $response = $this->postJson('/api/v1/public/manifestations', $payload, self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['attachments.0.uuid']);

        $this->assertSame(0, Manifestation::query()->count());
    }

    public function test_attachment_extension_mismatching_key_is_rejected(): void
    {
        $agency = DestinationAgenciesHelper::createTestDestinationAgency();
        $uuid = (string) Str::uuid();

        $payload = ManifestationsHelper::dumbPublicManifestationData($agency->uuid, [
            'attachments' => [$this->attachment($uuid, extension: 'jpg')],
        ]);

        $response = $this->postJson('/api/v1/public/manifestations', $payload, self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['attachments.0.extension']);

        $this->assertSame(0, Manifestation::query()->count());
    }

    public function test_attachment_with_unknown_extension_is_rejected(): void
    {
        $agency = DestinationAgenciesHelper::createTestDestinationAgency();
        $uuid = (string) Str::uuid();

        $payload = ManifestationsHelper::dumbPublicManifestationData($agency->uuid, [
            'attachments' => [$this->attachment($uuid, key: "public-manifestations/{$uuid}.php", extension: 'php')],
        ]);

        $response = $this->postJson('/api/v1/public/manifestations', $payload, self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['attachments.0.key', 'attachments.0.extension']);
    }

    public function test_attachment_with_wrong_bucket_is_rejected(): void
    {
        $agency = DestinationAgenciesHelper::createTestDestinationAgency();
        $uuid = (string) Str::uuid();

        $payload = ManifestationsHelper::dumbPublicManifestationData($agency->uuid, [
            'attachments' => [$this->attachment($uuid, bucket: 'someone-elses-bucket')],
        ]);

        $response = $this->postJson('/api/v1/public/manifestations', $payload, self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['attachments.0.bucket']);

        $this->assertSame(0, Manifestation::query()->count());
    }

    public function test_valid_attachment_is_stored_on_the_manifestation(): void
    {
        Storage::fake('central');
        Storage::fake('s3');

        $agency = DestinationAgenciesHelper::createTestDestinationAgency();
        $uuid = (string) Str::uuid();
        $key = "public-manifestations/{$uuid}.pdf";

        Storage::disk('central')->put($key, '%PDF-1.4 fake');

        $payload = ManifestationsHelper::dumbPublicManifestationData($agency->uuid, [
            'attachments' => [$this->attachment($uuid)],
        ]);

        $response = $this->postJson('/api/v1/public/manifestations', $payload, self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_CREATED);

        $manifestation = Manifestation::query()
            ->where('protocol_number', $response->json('protocol_number'))
            ->firstOrFail();

        $this->assertCount(1, $manifestation->getMedia('attachments'));
        $this->assertSame("{$uuid}.pdf", $manifestation->getFirstMedia('attachments')->file_name);

        // Even with an attachment on file, the citizen response says nothing about it.
        $this->assertArrayNotHasKey('attachments', $response->json());
    }

    public function test_create_response_exposes_only_public_keys(): void
    {
        $agency = DestinationAgenciesHelper::createTestDestinationAgency();

        $payload = ManifestationsHelper::dumbPublicManifestationData($agency->uuid);

        $response = $this->postJson('/api/v1/public/manifestations', $payload, self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertOnlyPublicKeys($response->json());
        $this->assertSame([], $response->json('logs'));
    }

    public function test_show_by_protocol_returns_only_public_keys_and_public_logs(): void
    {
        $manifestation = ManifestationsHelper::createTestManifestation([
            'is_anonymous' => false,
            'manifestant_name' => 'Maria da Silva',
            'manifestant_email' => 'maria@example.com',
            'manifestant_phone' => '(11) 99999-0000',
        ]);

        // Inserted newest-first so the ordering assertion is not satisfied by insertion order alone.
        ManifestationsHelper::createTestLog($manifestation, [
            'content' => 'Parecer final: buraco tapado.',
            'is_public' => true,
            'status' => ManifestationStatus::ANSWERED,
            'created_at' => now(),
        ]);
        ManifestationsHelper::createTestLog($manifestation, [
            'content' => 'Nota interna: encaminhar para a equipe de obras.',
            'is_public' => false,
            'status' => ManifestationStatus::UNDER_REVIEW,
            'created_at' => now()->subHours(2),
        ]);
        ManifestationsHelper::createTestLog($manifestation, [
            'content' => 'Manifestacao em analise.',
            'is_public' => true,
            'status' => ManifestationStatus::UNDER_REVIEW,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->getJson(
            "/api/v1/public/manifestations/{$manifestation->protocol_number}",
            self::PUBLIC_HEADERS
        );

        $response->assertStatus(Response::HTTP_OK);

        $this->assertOnlyPublicKeys($response->json());
        $this->assertSame($manifestation->protocol_number, $response->json('protocol_number'));

        $logs = $response->json('logs');
        $this->assertCount(2, $logs);
        $this->assertSame('Manifestacao em analise.', $logs[0]['content']);
        $this->assertSame('Parecer final: buraco tapado.', $logs[1]['content']);

        foreach ($logs as $log) {
            $this->assertSame(self::PUBLIC_LOG_KEYS, array_keys($log));
        }

        $this->assertStringNotContainsString('Nota interna', $response->getContent());
        $this->assertStringNotContainsString('Maria da Silva', $response->getContent());
        $this->assertStringNotContainsString('maria@example.com', $response->getContent());
    }

    public function test_show_with_unknown_protocol_returns_404(): void
    {
        $response = $this->getJson('/api/v1/public/manifestations/OUV-2026-DEADBEEF', self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_show_with_malformed_protocol_returns_404(): void
    {
        $manifestation = ManifestationsHelper::createTestManifestation();

        $this->getJson('/api/v1/public/manifestations/abc', self::PUBLIC_HEADERS)
            ->assertStatus(Response::HTTP_NOT_FOUND);

        // The route constraint must not be bypassed by a lowercase or otherwise ill-shaped protocol.
        $this->getJson('/api/v1/public/manifestations/' . strtolower($manifestation->protocol_number), self::PUBLIC_HEADERS)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_management_list_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/manifestations', self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_management_show_requires_authentication(): void
    {
        $manifestation = ManifestationsHelper::createTestManifestation();

        $response = $this->getJson("/api/v1/manifestations/{$manifestation->uuid}", self::PUBLIC_HEADERS);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    private function attachment(string $uuid, ?string $key = null, string $extension = 'pdf', ?string $bucket = null): array
    {
        return [
            'uuid' => $uuid,
            'bucket' => $bucket ?? self::CENTRAL_BUCKET,
            'key' => $key ?? "public-manifestations/{$uuid}.pdf",
            'extension' => $extension,
        ];
    }

    private function assertOnlyPublicKeys(array $body): void
    {
        $this->assertSame(self::PUBLIC_KEYS, array_keys($body));

        foreach (self::FORBIDDEN_KEYS as $key) {
            $this->assertArrayNotHasKey($key, $body);
        }
    }
}
