<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Common\Core\Models\Model;
use Modules\Questionnaires\Models\Concerns\QuestionnaireHistory;

final class Questionnaire extends Model
{
    use QuestionnaireHistory;

    protected $fillable = [
        'uuid',
        'questionnaires_group_id',
        'title',
        'description',
        'icon',
        'active',
        'version',
        'elements',
        'started_at',
        'expired_at',
    ];

    protected $casts = [
        'elements' => 'array',
        'version' => 'integer',
        'active' => 'boolean',
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    protected $nullable = [
        'description',
        'icon',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function questionnairesGroup(): BelongsTo
    {
        return $this->belongsTo(QuestionnairesGroup::class)->withoutGlobalScope('active-questionnaires-groups');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }
}
