<?php

declare(strict_types=1);

namespace Modules\Transport\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Common\Core\Models\Model;
use Modules\Transport\Support\VehicleFuel;

final class VehicleRefuel extends Model
{
    protected $fillable = [
        'uuid',
        'vehicle_id',
        'refueled_at',
        'odometer',
        'liters',
        'price_per_liter',
        'total_value',
        'fuel_type',
        'station_location',
        'consumption',
    ];

    protected $casts = [
        'refueled_at' => 'date',
        'liters' => 'float',
        'price_per_liter' => 'float',
        'total_value' => 'float',
        'fuel_type' => VehicleFuel::class,
        'station_location' => 'json',
        'consumption' => 'float',
    ];

    protected $nullable = [
        'station_location',
        'consumption',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
