<?php

declare(strict_types=1);

namespace Tests\Feature\Questionnaires;

use Illuminate\Http\Response;
use Mockery\MockInterface;
use Modules\Common\Core\Support\SignedStorageUrlService;
use Modules\Questionnaires\Models\Questionnaire;
use Tests\Feature\Questionnaires\Helpers\QuestionnairesGroupsHelper;
use Tests\TestCase;
use Tests\Traits\RefreshDatabaseWithTenant;

class PublicQuestionnaireSignedStorageUrlApiTest extends TestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_create_signed_storage_url_for_active_questionnaire_upload_field(): void
    {
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $questionnaire = new Questionnaire([
            'questionnaires_group_id' => $questionnairesGroup->id,
            'title' => 'test-questionnaire',
            'description' => 'test description',
            'active' => true,
            'elements' => [
                ['id' => 'f1', 'type' => 'FileUploadField', 'extraAttributes' => []],
            ],
            'version' => 1,
        ]);
        $questionnaire->save();

        $this->mock(SignedStorageUrlService::class, function (MockInterface $mock) use ($questionnaire) {
            $mock->shouldReceive('generate')
                ->once()
                ->withArgs(fn (string $key, string $contentType, string $visibility, ?string $uuid): bool => preg_match("/^public-questionnaires\/{$questionnaire->id}\/[0-9a-f-]{36}\.pdf$/", $key) === 1
                        && $contentType === 'application/pdf'
                        && $visibility === 'private'
                        && $uuid !== null
                        && str_contains($key, $uuid))
                ->andReturn([
                    'uuid' => 'mocked-uuid',
                    'bucket' => 'test-bucket',
                    'key' => 'mocked-key',
                    'url' => 'https://s3.example.test/mocked',
                    'headers' => ['Content-Type' => 'application/pdf'],
                ]);
        });

        $response = $this->postJson(
            "/api/v1/questionnaires/{$questionnaire->uuid}/uploads/signed-storage-url",
            [
                'field_id' => 'f1',
                'content_type' => 'application/pdf',
                'file_name' => 'documento.pdf',
                'file_size' => 1024,
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['uuid', 'bucket', 'key', 'url', 'headers'])
            ->assertJsonPath('bucket', 'test-bucket');
    }
}
