<?php

declare(strict_types=1);

namespace Modules\Transport\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Common\Core\Support\NotificationType;
use Modules\Transport\Models\VehicleRequest;

class VehicleRequestRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(private VehicleRequest $vehicleRequest) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Solicitação de veículo rejeitada',
            'message' => 'A solicitação de veículo para o dia ' . $this->vehicleRequest->departure_at->format('d/m/Y') . ' foi rejeitada.',
            'vehicle_request_id' => $this->vehicleRequest->uuid,
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'data' => $this->toArray($notifiable),
        ];
    }

    public function broadcastType(): string
    {
        return NotificationType::VEHICLE_REQUEST_REJECTED->value;
    }

    public function databaseType(): string
    {
        return NotificationType::VEHICLE_REQUEST_REJECTED->value;
    }
}
