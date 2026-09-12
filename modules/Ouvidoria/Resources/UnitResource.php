<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Common\Core\Resources\Concerns\LoadsRelationsIfMissing;

final class UnitResource extends JsonResource
{
    use LoadsRelationsIfMissing;

    public function toArray(Request $request): array
    {
        $unit_type = $this->loadIfMissing('unitType');

        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'active' => $this->active,
            'code' => $this->code,
            'unit_type' => $unit_type ? [
                'id' => $unit_type->uuid,
                'name' => $unit_type->name,
            ] : null,
            'cnpj' => $this->cnpj,
            'address' => $this->address,
            'contacts' => $this->contacts,
            'open_time' => $this->open_time,
            'close_time' => $this->close_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
