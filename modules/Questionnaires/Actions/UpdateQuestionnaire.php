<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Actions;

use Modules\Common\Core\Exceptions\ApiException;
use Modules\Questionnaires\DTOs\UpdateQuestionnaireDTO;
use Modules\Questionnaires\Models\Questionnaire;

final readonly class UpdateQuestionnaire
{
    public function __construct(
        private FetchQuestionnaire $fetchQuestionnaire,
    ) {}

    public function handle(string $uuid, UpdateQuestionnaireDTO $dto): Questionnaire
    {
        $questionnaire = $this->fetchQuestionnaire->handle($uuid);

        if ($questionnaire->active && (! isset($dto->active) || $dto->active === true)) {
            throw new ApiException('Não é possível editar um questionário ativo.', 400);
        }

        $updateData = $dto->nullableSafeToArray(Questionnaire::nullable());

        if (isset($updateData['active']) && $updateData['active'] === false && $questionnaire->active) {
            $updateData['version'] = $questionnaire->version + 1;
        }

        $questionnaire->fill($updateData);
        $questionnaire->save();

        return $questionnaire;
    }
}
