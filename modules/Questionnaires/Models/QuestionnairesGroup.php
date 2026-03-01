<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Common\Core\Models\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

final class QuestionnairesGroup extends Model implements Sortable
{
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'icon',
        'order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $nullable = [
        'description',
        'icon',
    ];

    protected $cascadeDeletes = [
        'questionnaires',
    ];

    public function scopeAll(Builder $query): Builder
    {
        return $query->withoutGlobalScope('active-questionnaires-groups');
    }

    public function questionnaires(): HasMany
    {
        return $this->hasMany(Questionnaire::class);
    }

    protected static function booted(): void
    {
        self::addGlobalScope(
            'active-questionnaires-groups',
            fn (Builder $builder) => $builder->where('active', true)
        );
    }
}
