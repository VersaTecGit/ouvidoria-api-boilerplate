<?php

declare(strict_types=1);

namespace Modules\Transport\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Auth\Models\User;
use Modules\Common\Core\Models\Model;
use Modules\Transport\Support\VehicleRequestPriority;
use Modules\Transport\Support\VehicleRequestStatus;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class VehicleRequest extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'requester_id',
        'origin',
        'destination',
        'waypoints',
        'departure_at',
        'return_at',
        'priority',
        'justification',
        'status',
        'protocol_number',
        'vehicle_id',
        'driver_id',
        'response',
    ];

    protected $casts = [
        'origin' => 'json',
        'destination' => 'json',
        'waypoints' => 'array',
        'departure_at' => 'datetime',
        'return_at' => 'datetime',
        'priority' => VehicleRequestPriority::class,
        'status' => VehicleRequestStatus::class,
    ];

    protected $nullable = [
        'waypoints',
        'return_at',
        'vehicle_id',
        'driver_id',
        'response',
    ];

    protected $cascadeDeletes = [
        'userPassengers',
        'vehicleTrip',
        'travelAllowances',
    ];

    public static function generateProtocolNumber(): string
    {
        $year = date('Y');
        $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        return "VHC-{$year}-{$random}";
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vehicleTrip(): HasOne
    {
        return $this->hasOne(VehicleTrip::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function travelAllowances(): HasMany
    {
        return $this->hasMany(VehicleRequestTravelAllowance::class);
    }

    public function userPassengers(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'vehicle_request_passenger');
    }
}
