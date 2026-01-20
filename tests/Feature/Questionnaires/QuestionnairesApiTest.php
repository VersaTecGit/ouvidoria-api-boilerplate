<?php

declare(strict_types=1);

namespace Tests\Feature\Questionnaires;

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Modules\Questionnaires\Models\Questionnaire;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Questionnaires\Helpers\QuestionnairesGroupsHelper;
use Tests\Feature\Questionnaires\Helpers\QuestionnairesHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class QuestionnairesApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_a_list_of_questionnaires(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_QUESTIONNAIRES->value]);
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        foreach (range(1, 10) as $number) {
            QuestionnairesHelper::createTestQuestionnaire($questionnairesGroup);
        }

        $response = $this->getJson(
            "/api/v1/questionnaires?questionnaires_group_id={$questionnairesGroup->uuid}",
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

    public function test_should_return_questionnaires_page_2(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_QUESTIONNAIRES->value]);
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        foreach (range(1, 30) as $number) {
            QuestionnairesHelper::createTestQuestionnaire($questionnairesGroup);
        }

        $response = $this->getJson(
            "/api/v1/questionnaires?questionnaires_group_id={$questionnairesGroup->uuid}&page=2",
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

    public function test_should_return_questionnaires_with_total_per_page(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_QUESTIONNAIRES->value]);
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        foreach (range(1, 30) as $number) {
            QuestionnairesHelper::createTestQuestionnaire($questionnairesGroup);
        }

        $response = $this->getJson(
            "/api/v1/questionnaires?questionnaires_group_id={$questionnairesGroup->uuid}&per_page=5",
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

    public function test_should_return_questionnaires_with_filter(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_QUESTIONNAIRES->value]);
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        foreach (range(1, 30) as $number) {
            QuestionnairesHelper::createTestQuestionnaire($questionnairesGroup);
        }

        Questionnaire::create(QuestionnairesHelper::dumbQuestionnaireData($questionnairesGroup));

        $response = $this->getJson(
            "/api/v1/questionnaires?questionnaires_group_id={$questionnairesGroup->uuid}&search=test-questionnaire",
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

    public function test_should_create_new_questionnaire(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_QUESTIONNAIRES->value]);
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $response = $this->postJson(
            '/api/v1/questionnaires',
            [
                'questionnaires_group_id' => $questionnairesGroup->uuid,
                'title' => 'Test Questionnaire',
                'description' => 'Test Description',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'id',
                'questionnaires_group_id',
                'title',
                'description',
                'icon',
                'version',
                'max_version',
                'active',
                'elements',
                'created_at',
                'updated_at',
                'started_at',
                'expired_at',
            ]);

        $this->assertTrue(
            Questionnaire::where('title', 'Test Questionnaire')
                ->where('description', 'Test Description')
                ->where('questionnaires_group_id', $questionnairesGroup->id)
                ->exists()
        );
    }

    public function test_should_validate_create_new_questionnaire(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_QUESTIONNAIRES->value]);

        $response = $this->postJson(
            '/api/v1/questionnaires',
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

    public function test_should_return_questionnaires_by_uuid(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_QUESTIONNAIRES->value]);
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $questionnaire = QuestionnairesHelper::createTestQuestionnaire($questionnairesGroup);

        $response = $this->getJson(
            "/api/v1/questionnaires/{$questionnaire->uuid}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'questionnaires_group_id',
                'title',
                'description',
                'icon',
                'version',
                'max_version',
                'active',
                'elements',
                'created_at',
                'updated_at',
                'started_at',
                'expired_at',
            ]);
    }

    public function test_should_return_questionnaires_by_uuid_when_inactive(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_QUESTIONNAIRES->value]);
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $questionnaire = QuestionnairesHelper::createTestQuestionnaire($questionnairesGroup);

        $questionnaire->update(['active' => false]);

        $response = $this->getJson(
            "/api/v1/questionnaires/{$questionnaire->uuid}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'questionnaires_group_id',
                'title',
                'description',
                'icon',
                'version',
                'max_version',
                'active',
                'elements',
                'created_at',
                'updated_at',
                'started_at',
                'expired_at',
            ]);
    }

    public function test_should_throw_exception_when_not_has_permission_to_view_inactive_questionnaire(): void
    {
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $questionnaire = QuestionnairesHelper::createTestQuestionnaire($questionnairesGroup);

        $questionnaire->update(['active' => false]);

        $response = $this->getJson(
            "/api/v1/questionnaires/{$questionnaire->uuid}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function test_should_return_questionnaires_by_uuid_when_active(): void
    {
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $questionnaire = QuestionnairesHelper::createTestQuestionnaire($questionnairesGroup);
        $questionnaire->update(['active' => true]);

        $response = $this->getJson(
            "/api/v1/questionnaires/{$questionnaire->uuid}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'questionnaires_group_id',
                'title',
                'description',
                'icon',
                'version',
                'max_version',
                'active',
                'elements',
                'created_at',
                'updated_at',
                'started_at',
                'expired_at',
            ]);
    }

    public function test_should_return_404_when_not_exists_questionnaires(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_QUESTIONNAIRES->value]);

        $response = $this->getJson(
            '/api/v1/questionnaires/00000000-0000-0000-0000-000000000000',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_update_questionnaire(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_QUESTIONNAIRES->value]);
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $questionnaire = QuestionnairesHelper::createTestQuestionnaire($questionnairesGroup);

        $questionnaire->update(['active' => false]);

        $response = $this->putJson(
            "/api/v1/questionnaires/{$questionnaire->uuid}",
            [
                'title' => 'Test Questionnaire 2',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['title' => 'Test Questionnaire 2']);
    }

    public function test_should_generate_new_version_when_updating_questionnaire(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_QUESTIONNAIRES->value]);
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $questionnaire = QuestionnairesHelper::createTestQuestionnaire($questionnairesGroup);

        $response = $this->putJson(
            "/api/v1/questionnaires/{$questionnaire->uuid}",
            [
                'title' => 'Test Questionnaire 2',
                'active' => false,
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['active' => false]);

        $this->assertTrue(
            Questionnaire::where('uuid', $questionnaire->uuid)
                ->where('title', $questionnaire->title)
                ->where('version', 1)
                ->whereNotNull('expired_at')
                ->exists()
        );

        $this->assertTrue(
            Questionnaire::where('uuid', $questionnaire->uuid)
                ->where('title', 'Test Questionnaire 2')
                ->where('version', 2)
                ->where('expired_at', null)
                ->exists()
        );
    }

    public function test_should_throw_exception_when_deleting_active_questionnaire(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::DELETE_QUESTIONNAIRES->value]);
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $questionnaire = QuestionnairesHelper::createTestQuestionnaire($questionnairesGroup);

        $response = $this->deleteJson(
            "/api/v1/questionnaires/{$questionnaire->uuid}",
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_should_delete_questionnaire(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::DELETE_QUESTIONNAIRES->value]);
        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $questionnaire = QuestionnairesHelper::createTestQuestionnaire($questionnairesGroup);

        $questionnaire->update(['active' => false]);

        $response = $this->deleteJson(
            "/api/v1/questionnaires/{$questionnaire->uuid}",
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
