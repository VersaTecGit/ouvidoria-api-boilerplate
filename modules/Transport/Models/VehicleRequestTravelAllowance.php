<?php

declare(strict_types=1);

namespace Modules\Transport\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Common\Core\Models\Model;

final class VehicleRequestTravelAllowance extends Model
{
    protected $fillable = [
        'uuid',
        'vehicle_request_id',
        'beneficiary_type',
        'beneficiary_id',
        'purpose',
        'days_requested',
        'unit_value',
    ];

    protected $casts = [
        'days_requested' => 'float',
        'unit_value' => 'float',
    ];

    public function vehicleRequest(): BelongsTo
    {
        return $this->belongsTo(VehicleRequest::class);
    }

    public function beneficiary(): MorphTo
    {
        return $this->morphTo();
    }
}
