<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Actions;

use Modules\Questionnaires\Models\QuestionnairesGroup;

final readonly class FetchQuestionnairesGroup
{
    public function handle(string $uuid): QuestionnairesGroup
    {
        return QuestionnairesGroup::findAllByUuid($uuid);
    }
}
