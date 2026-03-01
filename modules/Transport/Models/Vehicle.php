<?php

declare(strict_types=1);

namespace Modules\Transport\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Common\Core\Models\Model;
use Modules\Transport\Support\VehicleStatus;
use Modules\Transport\Support\VehicleType;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Vehicle extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'required_license_categories',
        'plate',
        'model',
        'brand',
        'capacity',
        'color',
        'fuels',
        'manufacture_year',
        'renavam',
        'chassis_number',
        'type',
        'other_type',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'required_license_categories' => 'array',
        'fuels' => 'array',
        'type' => VehicleType::class,
        'status' => VehicleStatus::class,
    ];

    protected $nullable = [
        'color',
        'fuels',
        'other_type',
    ];

    protected $cascadeDeletes = [
        'documents',
        'vehicleRequests',
        'refuels',
        'maintenances',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('pictures');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    public function vehicleRequests(): HasMany
    {
        return $this->hasMany(VehicleRequest::class);
    }

    public function refuels(): HasMany
    {
        return $this->hasMany(VehicleRefuel::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class);
    }
}
