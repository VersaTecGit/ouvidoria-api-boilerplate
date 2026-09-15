<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;
use Modules\Common\Core\Models\Model;
use Modules\Ouvidoria\Support\ManifestationStatus;

/**
 * The timeline entry. A log with `is_public = true` is what the citizen reads
 * as "Histórico de Andamento"; everything else is an internal note and must
 * never reach the public endpoint.
 */
final class ManifestationLog extends Model
{
    protected $fillable = [
        'uuid',
        'manifestation_id',
        'content',
        'is_public',
        'status',
        'author_id',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'status' => ManifestationStatus::class,
    ];

    protected $nullable = [
        'status',
        'author_id',
    ];

    public function manifestation(): BelongsTo
    {
        return $this->belongsTo(Manifestation::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')
            ->withoutGlobalScope('active-users');
    }
}
