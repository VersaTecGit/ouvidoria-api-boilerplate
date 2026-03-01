<?php

declare(strict_types=1);

namespace Modules\Transport\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Auth\Models\User;
use Modules\Common\Core\Models\Model;
use Modules\Transport\Support\VehicleTripStatus;

final class VehicleTrip extends Model
{
    protected $fillable = [
        'uuid',
        'vehicle_request_id',
        'status',
        'started_at',
        'finished_at',
        'occurrences',
    ];

    protected $casts = [
        'status' => VehicleTripStatus::class,
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'occurrences' => 'array',
    ];

    protected $nullable = [
        'started_at',
        'finished_at',
        'occurrences',
    ];

    protected $cascadeDeletes = [
        'userPassengers',
    ];

    public function vehicleRequest(): BelongsTo
    {
        return $this->belongsTo(VehicleRequest::class);
    }

    public function userPassengers(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'vehicle_trip_passenger')->withPivot('was_present', 'absence_reason');
    }
}
