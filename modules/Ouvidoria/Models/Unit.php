<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Auth\Models\User;
use Modules\Common\Core\Models\Model;

final class Unit extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'active',
        'code',
        'unit_type_id',
        'cnpj',
        'address',
        'contacts',
        'open_time',
        'close_time',
    ];

    protected $casts = [
        'active' => 'boolean',
        'address' => 'array',
        'contacts' => 'array',
    ];

    protected $nullable = [
        'description',
        'cnpj',
        'address',
        'contacts',
        'open_time',
        'close_time',
    ];

    public function unitType(): BelongsTo
    {
        return $this->belongsTo(UnitType::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withoutGlobalScope('active-users');
    }

    protected static function booted(): void
    {
        self::addGlobalScope(
            'active-units',
            fn (Builder $builder) => $builder->where('active', true)
        );
    }
}
