<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;
use Modules\Auth\Models\User;

Broadcast::channel('user.{uuid}', fn (User $user, string $uuid): bool => $user->uuid === $uuid);
