<?php

declare(strict_types=1);

namespace Modules\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TwoFactorEnabledResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'secret' => $this->resource['secret'],
            'qr_code_url' => $this->resource['qr_code_url'],
            'recovery_codes' => $this->resource['recovery_codes'],
        ];
    }
}
