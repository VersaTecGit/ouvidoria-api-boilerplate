<?php

declare(strict_types=1);

namespace Modules\Common\Core\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Common\Core\Models\Concerns\CommonQueries;
use Modules\Common\Core\Models\Concerns\Filterable;
use Modules\Common\Core\Models\Concerns\HasUuids;
use Modules\Common\Core\Models\Concerns\LogChanges;
use Modules\Common\Core\Models\Concerns\UserActions;

abstract class Model extends BaseModel
{
    use CommonQueries,
        Filterable,
        HasUuids,
        LogChanges,
        SoftDeletes,
        UserActions;

    protected $nullable = [];

    public static function nullable(): array
    {
        $model = static::query()->getModel();

        return $model->nullable;
    }

    public static function findByUuid(string $uuid): ?self
    {
        return static::where('uuid', $uuid)->firstOrFail();
    }
}
