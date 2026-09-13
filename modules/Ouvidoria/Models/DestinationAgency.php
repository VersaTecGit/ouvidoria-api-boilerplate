<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Models;

use Illuminate\Database\Eloquent\Builder;
use Modules\Common\Core\Models\Model;

final class DestinationAgency extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'active',
        'order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    protected $nullable = [];

    protected static function booted(): void
    {
        self::addGlobalScope(
            'active-destination-agencies',
            fn (Builder $builder) => $builder->where('active', true)
        );
    }
}
