<?php

declare(strict_types=1);

namespace Modules\Common\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountUnreadNotificationsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'unread_count' => $this->resource['unread_count'],
            'messages_unread_count' => $this->resource['messages_unread_count'],
        ];
    }
}
