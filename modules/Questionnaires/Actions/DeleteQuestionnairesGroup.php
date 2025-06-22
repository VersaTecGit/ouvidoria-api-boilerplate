<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Actions;

use Modules\Common\Core\Exceptions\ApiException;

final readonly class DeleteQuestionnairesGroup
{
    public function __construct(private FetchQuestionnairesGroup $fetchQuestionnairesGroup) {}

    public function handle(string $uuid): void
    {
        $questionnairesGroup = $this->fetchQuestionnairesGroup->handle($uuid);

        if ($questionnairesGroup->active) {
            throw new ApiException('Não é possível deletar um questionário ativo.', 400);
        }

        foreach ($questionnairesGroup->questionnaires as $questionnaire) {
            if ($questionnaire->active) {
                throw new ApiException('Não é possível deletar um grupo de questionários com questionários ativos.', 400);
            }
        }

        $questionnairesGroup->delete();
    }
}
