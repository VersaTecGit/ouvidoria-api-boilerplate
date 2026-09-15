<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Ouvidoria\Models\Manifestation;

/**
 * Lookup by protocol for the anonymous citizen. Deliberately separate from
 * `FetchManifestation`: that one eager-loads staff-only relations (agency,
 * unit, responder, every log with its author), none of which may be touched
 * on the public path. Only the public timeline is loaded here.
 */
final readonly class FetchPublicManifestation
{
    public function handle(string $protocol): Manifestation
    {
        return Manifestation::query()
            ->with(['publicLogs' => fn (HasMany $query) => $query->oldest()->orderBy('id')])
            ->where('protocol_number', $protocol)
            ->firstOrFail();
    }
}
