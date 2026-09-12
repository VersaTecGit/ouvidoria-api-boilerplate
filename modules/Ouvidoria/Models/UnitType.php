<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Common\Core\Models\Model;

final class UnitType extends Model
{
    protected $fillable = [
        'uuid',
        'name',
    ];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
}
