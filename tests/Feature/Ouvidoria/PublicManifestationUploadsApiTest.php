<?php

declare(strict_types=1);

namespace Tests\Feature\Ouvidoria;

use Illuminate\Http\Response;
use Mockery\MockInterface;
use Modules\Common\Core\Support\SignedStorageUrlService;
use Tests\TestCase;
use Tests\Traits\RefreshDatabaseWithTenant;

class PublicManifestationUploadsApiTest extends TestCase
{
    use RefreshDatabaseWithTenant;

    private const URL = '/api/v1/public/manifestations/uploads/signed-storage-url';

    private const HEADERS = [
        'X-Domain' => 'foo',
        'Accept' => 'application/json',
    ];

    public function test_should_create_signed_storage_url_for_pdf_without_token(): void
    {
        $this->mockServiceExpectingKeyWithExtension('pdf', 'application/pdf');

        $response = $this->postJson(self::URL, [
            'content_type' => 'application/pdf',
            'file_name' => 'documento.pdf',
            'file_size' => 2048,
        ], self::HEADERS);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['uuid', 'bucket', 'key', 'url', 'headers'])
            ->assertJsonPath('bucket', 'test-bucket')
            ->assertJsonPath('headers.Content-Type', 'application/pdf');
    }

    public function test_should_derive_jpg_extension_from_content_type_not_from_file_name(): void
    {
        $this->mockServiceExpectingKeyWithExtension('jpg', 'image/jpeg');

        $response = $this->postJson(self::URL, [
            'content_type' => 'image/jpeg',
            'file_name' => 'foto.JPEG',
            'file_size' => 2048,
        ], self::HEADERS);

        $response->assertStatus(Response::HTTP_CREATED);
    }

    public function test_should_derive_png_extension_from_content_type(): void
    {
        $this->mockServiceExpectingKeyWithExtension('png', 'image/png');

        $response = $this->postJson(self::URL, [
            'content_type' => 'image/png',
            'file_name' => 'captura.png',
            'file_size' => 2048,
        ], self::HEADERS);

        $response->assertStatus(Response::HTTP_CREATED);
    }

    public function test_should_reject_disallowed_content_type_without_calling_service(): void
    {
        $this->mockServiceNeverCalled();

        $response = $this->postJson(self::URL, [
            'content_type' => 'application/x-msdownload',
            'file_name' => 'programa.exe',
            'file_size' => 2048,
        ], self::HEADERS);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('message', 'Tipo de arquivo não permitido.');
    }

    public function test_should_reject_html_content_type_even_with_png_file_name(): void
    {
        $this->mockServiceNeverCalled();

        $response = $this->postJson(self::URL, [
            'content_type' => 'text/html',
            'file_name' => 'imagem.png',
            'file_size' => 2048,
        ], self::HEADERS);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('message', 'Tipo de arquivo não permitido.');
    }

    public function test_should_reject_file_above_10mb_without_calling_service(): void
    {
        $this->mockServiceNeverCalled();

        $response = $this->postJson(self::URL, [
            'content_type' => 'application/pdf',
            'file_name' => 'grande.pdf',
            'file_size' => 10 * 1024 * 1024 + 1,
        ], self::HEADERS);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('message', 'Arquivo excede o tamanho máximo permitido.');
    }

    public function test_should_validate_required_fields(): void
    {
        $this->mockServiceNeverCalled();

        $response = $this->postJson(self::URL, [], self::HEADERS);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['content_type', 'file_name', 'file_size']);
    }

    public function test_should_expose_rate_limit_of_10_requests_per_minute(): void
    {
        $this->mockServiceExpectingKeyWithExtension('pdf', 'application/pdf');

        $response = $this->postJson(self::URL, [
            'content_type' => 'application/pdf',
            'file_name' => 'documento.pdf',
            'file_size' => 2048,
        ], self::HEADERS);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertHeader('X-RateLimit-Limit', '10');
    }

    private function mockServiceExpectingKeyWithExtension(string $extension, string $contentType): void
    {
        $this->mock(SignedStorageUrlService::class, function (MockInterface $mock) use ($extension, $contentType) {
            $mock->shouldReceive('generate')
                ->once()
                ->withArgs(fn (string $key, string $actualContentType, string $visibility, ?string $uuid): bool => preg_match("/^public-manifestations\/([0-9a-f-]{36})\.{$extension}$/", $key, $matches) === 1
                        && $actualContentType === $contentType
                        && $visibility === 'private'
                        && $uuid === $matches[1])
                ->andReturn([
                    'uuid' => 'mocked-uuid',
                    'bucket' => 'test-bucket',
                    'key' => "public-manifestations/mocked-uuid.{$extension}",
                    'url' => 'https://s3.example.test/mocked',
                    'headers' => ['Content-Type' => $contentType],
                ]);
        });
    }

    private function mockServiceNeverCalled(): void
    {
        $this->mock(SignedStorageUrlService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('generate');
        });
    }
}
