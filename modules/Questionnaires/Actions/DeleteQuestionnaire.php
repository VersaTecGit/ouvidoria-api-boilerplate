<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Actions;

use Modules\Common\Core\Exceptions\ApiException;

final readonly class DeleteQuestionnaire
{
    public function __construct(
        private FetchQuestionnaire $fetchQuestionnaire,
    ) {}

    public function handle(string $uuid): void
    {
        $questionnaire = $this->fetchQuestionnaire->handle($uuid);

        if ($questionnaire->active) {
            throw new ApiException('Não é possível deletar um questionário ativo.', 400);
        }

        $questionnaire->delete();
    }
}
