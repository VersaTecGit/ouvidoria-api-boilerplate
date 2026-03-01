<?php

declare(strict_types=1);

namespace Modules\Transport\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Common\Core\Models\Model;
use Modules\Transport\Support\VehicleMaintenanceService;
use Modules\Transport\Support\VehicleMaintenanceStatus;
use Modules\Transport\Support\VehicleMaintenanceType;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class VehicleMaintenance extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'vehicle_id',
        'type',
        'service',
        'other_service',
        'performed_at',
        'scheduled_at',
        'workshop',
        'description',
        'cost',
        'status',
    ];

    protected $casts = [
        'type' => VehicleMaintenanceType::class,
        'service' => VehicleMaintenanceService::class,
        'performed_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'cost' => 'float',
        'status' => VehicleMaintenanceStatus::class,
    ];

    protected $nullable = [
        'other_service',
        'performed_at',
        'scheduled_at',
        'workshop',
        'description',
        'cost',
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
