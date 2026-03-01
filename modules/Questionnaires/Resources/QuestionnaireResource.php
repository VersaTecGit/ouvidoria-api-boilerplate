<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Common\Core\Resources\Concerns\LoadsRelationsIfMissing;
use Modules\Questionnaires\Models\Questionnaire;

final class QuestionnaireResource extends JsonResource
{
    use LoadsRelationsIfMissing;

    public function toArray(Request $request): array
    {
        $group = $this->loadIfMissing('questionnairesGroup');

        return [
            'id' => $this->uuid,
            'questionnaires_group_id' => $group->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'icon' => $this->icon,
            'version' => $this->version,
            'max_version' => Questionnaire::where('uuid', $this->uuid)->max('version'),
            'active' => $this->active,
            'elements' => $this->elements,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'started_at' => $this->started_at,
            'expired_at' => $this->expired_at,
        ];
    }
}
