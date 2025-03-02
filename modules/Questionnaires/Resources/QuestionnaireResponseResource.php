<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

final class QuestionnaireResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'questionnaire_id' => $this->questionnaire->uuid,
            'version' => $this->version,
            'answers' => $this->answers,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'attachments' => $this->getMedia('attachments')->map(fn ($media) => $media->getTemporaryUrl(Carbon::now()->addHours(2))),
        ];
    }
}
