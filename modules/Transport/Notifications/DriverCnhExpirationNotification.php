<?php

declare(strict_types=1);

namespace Modules\Transport\Notifications;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Auth\Models\User;
use Modules\Common\Core\Support\NotificationType;

class DriverCnhExpirationNotification extends Notification
{
    use Queueable;

    public function __construct(private User $user, private CarbonImmutable $expiration) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'CNH prestes a vencer',
            'message' => "A CNH do operador {$this->user->name} vencerá em {$this->expiration->format('d/m/Y')}.",
            'user_id' => $this->user->uuid,
            'expiration_date' => $this->expiration->toDateString(),
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
        return NotificationType::DRIVER_CNH_EXPIRATION->value;
    }

    public function databaseType(): string
    {
        return NotificationType::DRIVER_CNH_EXPIRATION->value;
    }
}
