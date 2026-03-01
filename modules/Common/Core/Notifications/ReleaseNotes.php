<?php

declare(strict_types=1);

namespace Modules\Common\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Common\Core\Support\NotificationType;

class ReleaseNotes extends Notification
{
    use Queueable;

    public function __construct(private string $version) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nova atualização disponível',
            'message' => "O Versa Social foi atualizado para a versão {$this->version}. Confira as novidades!",
            'version' => $this->version,
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
        return NotificationType::RELEASE_NOTES->value;
    }

    public function databaseType(): string
    {
        return NotificationType::RELEASE_NOTES->value;
    }
}
