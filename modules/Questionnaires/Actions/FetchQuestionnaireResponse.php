<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Actions;

use Modules\Questionnaires\Models\QuestionnaireResponse;

final readonly class FetchQuestionnaireResponse
{
    public function handle(string $uuid): QuestionnaireResponse
    {
        return QuestionnaireResponse::findAllByUuid($uuid);
    }
}
