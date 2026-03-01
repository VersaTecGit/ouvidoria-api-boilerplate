<?php

declare(strict_types=1);

namespace Modules\Common\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Auth\Models\User;
use Modules\Common\Core\Models\Concerns\CommonQueries;
use Modules\Common\Core\Support\ChangeAction;

final class ChangeLog extends Model
{
    use CommonQueries;

    protected $fillable = [
        'user_id',
        'record_id',
        'table',
        'action',
        'payload',
        'old_data',
        'new_data',
        'changed_data',
    ];

    protected $casts = [
        'action' => ChangeAction::class,
        'payload' => 'array',
        'old_data' => 'array',
        'new_data' => 'array',
        'changed_data' => 'array',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class)->withoutGlobalScope('active-users');
    }
}
