<?php

declare(strict_types=1);

namespace Modules\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class LoginResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (isset($this->resource['two_factor_required']) && $this->resource['two_factor_required']) {
            return [
                'two_factor_required' => true,
                'uuid' => $this->resource['uuid'],
            ];
        }

        return [
            'type' => $this->resource['type'],
            'token' => $this->resource['token'],
            'redirect' => $this->whenNotNull($this->resource['redirect'] ?? null),
            'force_change_password' => $this->whenNotNull($this->resource['force_change_password'] ?? null),
        ];
    }
}
