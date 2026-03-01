<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Common\Core\Models\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class QuestionnaireResponse extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'questionnaire_id',
        'version',
        'answers',
        'started_at',
        'ended_at',
    ];

    protected $nullable = [
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'version' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }
}
