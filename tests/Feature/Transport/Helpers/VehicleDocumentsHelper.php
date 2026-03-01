<?php

declare(strict_types=1);

namespace Tests\Feature\Transport\Helpers;

use Modules\Transport\Models\Vehicle;
use Modules\Transport\Models\VehicleDocument;
use Modules\Transport\Support\VehicleDocumentType;

class VehicleDocumentsHelper
{
    public static function createTestVehicleDocument(?Vehicle $vehicle = null): VehicleDocument
    {
        $vehicle ??= VehiclesHelper::createTestVehicle();

        $vehicle = new VehicleDocument([
            'vehicle_id' => $vehicle->id,
            'type' => VehicleDocumentType::IPVA->value,
            'attributes' => [],
        ]);

        $vehicle->save();

        return $vehicle;
    }

    public static function dumbVehicleDocumentData(?Vehicle $vehicle = null): array
    {
        $vehicle ??= VehiclesHelper::createTestVehicle();

        return [
            'vehicle_id' => $vehicle->id,
            'type' => VehicleDocumentType::IPVA->value,
            'attributes' => [],
        ];
    }
}
