<?php

declare(strict_types=1);

namespace Tests\Feature\Questionnaires;

use Illuminate\Http\Response;
use Modules\Auth\Support\Permissions;
use Modules\Questionnaires\Models\QuestionnaireResponse;
use Tests\AuthenticatedTestCase;
use Tests\Feature\Questionnaires\Helpers\QuestionnaireResponsesHelper;
use Tests\Feature\Questionnaires\Helpers\QuestionnairesGroupsHelper;
use Tests\Feature\Questionnaires\Helpers\QuestionnairesHelper;
use Tests\Traits\RefreshDatabaseWithTenant;

class QuestionnaireResponsesApiTest extends AuthenticatedTestCase
{
    use RefreshDatabaseWithTenant;

    public function test_should_return_a_list_of_questionnaires_responses(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_QUESTIONNAIRE_RESPONSES->value]);

        foreach (range(1, 10) as $number) {
            QuestionnaireResponsesHelper::createTestQuestionnaireResponse();
        }

        $response = $this->getJson(
            '/api/v1/questionnaires/responses',
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

    public function test_should_return_questionnaires_responses_page_2(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_QUESTIONNAIRE_RESPONSES->value]);

        foreach (range(1, 30) as $number) {
            QuestionnaireResponsesHelper::createTestQuestionnaireResponse();
        }

        $response = $this->getJson(
            '/api/v1/questionnaires/responses?page=2',
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

    public function test_should_return_questionnaires_responses_with_total_per_page(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::LIST_QUESTIONNAIRE_RESPONSES->value]);

        foreach (range(1, 30) as $number) {
            QuestionnaireResponsesHelper::createTestQuestionnaireResponse();
        }

        $response = $this->getJson(
            '/api/v1/questionnaires/responses?per_page=5',
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

    public function test_should_create_new_questionnaire_response(): void
    {
        $questionnaire = QuestionnairesHelper::createTestQuestionnaire(QuestionnairesGroupsHelper::createTestQuestionnairesGroup());

        $response = $this->postJson(
            '/api/v1/questionnaires/responses',
            [
                'questionnaire_id' => $questionnaire->uuid,
                'answers' => [],
            ],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ],
        );

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'id',
                'questionnaire_id',
                'version',
                'answers',
                'started_at',
                'ended_at',
                'created_at',
                'updated_at',
            ]);

        $this->assertTrue(
            QuestionnaireResponse::where('questionnaire_id', $questionnaire->id)
                ->where('version', $questionnaire->version)
                ->whereNotNull('started_at')
                ->whereNotNull('ended_at')
                ->exists()
        );
    }

    public function test_should_validate_create_new_questionnaire_response(): void
    {

        $response = $this->postJson(
            '/api/v1/questionnaires/responses',
            [],
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['questionnaire_id']);
    }

    public function test_should_return_questionnaires_responses_by_uuid(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_QUESTIONNAIRE_RESPONSES->value]);

        $questionnaireResponse = QuestionnaireResponsesHelper::createTestQuestionnaireResponse();

        $response = $this->getJson(
            "/api/v1/questionnaires/responses/{$questionnaireResponse->uuid}",
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'questionnaire_id',
                'version',
                'answers',
                'started_at',
                'ended_at',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_should_return_404_when_not_exists_questionnaires(): void
    {
        $token = $this->loginAndGetTokenWithPermissions([Permissions::VIEW_QUESTIONNAIRE_RESPONSES->value]);

        $response = $this->getJson(
            '/api/v1/questionnaires/responses/00000000-0000-0000-0000-000000000000',
            [
                'X-Domain' => 'foo',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
