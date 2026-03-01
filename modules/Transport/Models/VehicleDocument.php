<?php

declare(strict_types=1);

namespace Modules\Transport\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Common\Core\Models\Model;
use Modules\Transport\Support\VehicleDocumentType;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class VehicleDocument extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'vehicle_id',
        'type',
        'attributes',
    ];

    protected $casts = [
        'type' => VehicleDocumentType::class,
        'attributes' => 'json',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('files');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
