<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Auth\Models\User;
use Modules\Common\Core\Models\Model;
use Modules\Ouvidoria\Support\ManifestationStatus;
use Modules\Ouvidoria\Support\ManifestationType;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Manifestation extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'protocol_number',
        'type',
        'status',
        'destination_agency_id',
        'unit_id',
        'subject',
        'description',
        'occurrence_place',
        'is_anonymous',
        'user_id',
        'manifestant_name',
        'manifestant_email',
        'manifestant_phone',
        'manifestant_document',
        'manifestant_address',
        'parecer',
        'responded_by_id',
        'responded_at',
    ];

    protected $casts = [
        'type' => ManifestationType::class,
        'status' => ManifestationStatus::class,
        'is_anonymous' => 'boolean',
        'manifestant_address' => 'array',
        'responded_at' => 'datetime',
    ];

    protected $nullable = [
        'unit_id',
        'user_id',
        'manifestant_name',
        'manifestant_email',
        'manifestant_phone',
        'manifestant_document',
        'manifestant_address',
        'parecer',
        'responded_by_id',
        'responded_at',
    ];

    protected $cascadeDeletes = [
        'logs',
    ];

    /**
     * The protocol is the citizen's only way back to an anonymous manifestation.
     * `random_bytes` is a CSPRNG and all 4 bytes survive the hex+substr, so this
     * carries 32 bits of real entropy and is not guessable by enumeration.
     */
    public static function generateProtocolNumber(): string
    {
        $year = date('Y');
        $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        return "OUV-{$year}-{$random}";
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }

    public function destinationAgency(): BelongsTo
    {
        return $this->belongsTo(DestinationAgency::class)
            ->withoutGlobalScope('active-destination-agencies');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withoutGlobalScope('active-units');
    }

    /**
     * Reserved for the future citizen portal: an identified manifestant who
     * later becomes a User is linked here. Always null today.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withoutGlobalScope('active-users');
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by_id')
            ->withoutGlobalScope('active-users');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ManifestationLog::class);
    }

    public function publicLogs(): HasMany
    {
        return $this->logs()->where('is_public', true);
    }
}
