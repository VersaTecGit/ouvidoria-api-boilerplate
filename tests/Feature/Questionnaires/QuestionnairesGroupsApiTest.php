<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Modules\Questionnaires\Models\QuestionnairesGroup;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Questionnaires\Helpers\QuestionnairesGroupsHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class QuestionnairesGroupsApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_a_list_of_questionnaires_groups(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_QUESTIONNAIRES_GROUPS->value]);

        foreach (range(1, 20) as $number) {
            QuestionnairesGroupsHelper::createTestQuestionnairesGroup();
        }

        $response = $this->getJson(
            '/api/v1/questionnaires/groups',
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
        $this->assertCount(20, $responseData);

        $this->assertEquals(1, $responseMeta['current_page']);
        $this->assertEquals(1, $responseMeta['last_page']);
        $this->assertEquals(20, $responseMeta['per_page']);
        $this->assertEquals(20, $responseMeta['total']);
    }

    public function test_should_return_questionnaires_groups_page_2(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_QUESTIONNAIRES_GROUPS->value]);

        foreach (range(1, 30) as $number) {
            QuestionnairesGroupsHelper::createTestQuestionnairesGroup();
        }

        $response = $this->getJson(
            '/api/v1/questionnaires/groups?page=2',
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

    public function test_should_return_questionnaires_groups_with_total_per_page(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_QUESTIONNAIRES_GROUPS->value]);

        foreach (range(1, 30) as $number) {
            QuestionnairesGroupsHelper::createTestQuestionnairesGroup();
        }

        $response = $this->getJson(
            '/api/v1/questionnaires/groups?per_page=5',
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

    public function test_should_return_questionnaires_groups_with_filter(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_QUESTIONNAIRES_GROUPS->value]);

        foreach (range(1, 30) as $number) {
            QuestionnairesGroupsHelper::createTestQuestionnairesGroup();
        }

        QuestionnairesGroup::create(QuestionnairesGroupsHelper::dumbQuestionnairesGroupData());

        $response = $this->getJson(
            '/api/v1/questionnaires/groups?search=test-questionnaires-group',
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

    public function test_should_create_new_questionnaires_group(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_QUESTIONNAIRES_GROUPS->value]);

        $response = $this->postJson(
            '/api/v1/questionnaires/groups',
            [
                'title' => 'Test Questionnaires Group',
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

            ]);

        $this->assertTrue(
            QuestionnairesGroup::where('title', 'Test Questionnaires Group')
                ->where('description', 'Test Description')
                ->exists()
        );
    }

    public function test_should_validate_create_new_questionnaires_group(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::CREATE_QUESTIONNAIRES_GROUPS->value]);

        $response = $this->postJson(
            '/api/v1/questionnaires/groups',
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

    public function test_should_return_questionnaires_groups_by_uuid(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_QUESTIONNAIRES_GROUPS->value]);

        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $response = $this->getJson(
            "/api/v1/questionnaires/groups/{$questionnairesGroup->uuid}",
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
                'description',
                'icon',
                'order',
                'active',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_should_return_404_when_not_exists_questionnaires_groups(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_QUESTIONNAIRES_GROUPS->value]);

        $response = $this->getJson(
            '/api/v1/questionnaires/groups/00000000-0000-0000-0000-000000000000',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_update_questionnaires_group(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::EDIT_QUESTIONNAIRES_GROUPS->value]);

        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $response = $this->putJson(
            "/api/v1/questionnaires/groups/{$questionnairesGroup->uuid}",
            [
                'title' => 'Test Questionnaires Group 2',
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['title' => 'Test Questionnaires Group 2']);
    }

    public function test_should_throw_exception_when_deleting_active_questionnaires_group(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::DELETE_QUESTIONNAIRES_GROUPS->value]);

        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $response = $this->deleteJson(
            "/api/v1/questionnaires/groups/{$questionnairesGroup->uuid}",
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_should_delete_questionnaires_group(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::DELETE_QUESTIONNAIRES_GROUPS->value]);

        $questionnairesGroup = QuestionnairesGroupsHelper::createTestQuestionnairesGroup();

        $questionnairesGroup->update(['active' => false]);

        $response = $this->deleteJson(
            "/api/v1/questionnaires/groups/{$questionnairesGroup->uuid}",
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
