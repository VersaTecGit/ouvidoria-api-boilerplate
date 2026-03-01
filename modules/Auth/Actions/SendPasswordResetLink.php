<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\SendPasswordResetLinkDTO;
use Modules\Auth\Models\User;
use Modules\Tenant\Models\Theme;

final readonly class SendPasswordResetLink
{
    public function handle(SendPasswordResetLinkDTO $dto): string
    {
        ResetPassword::toMailUsing(fn (User $user, string $token) => (new MailMessage())
            ->subject('Recuperação de Senha')
            ->view('emails.reset-password', [
                'url' => $dto->callback_url . "{$token}?login={$user->login}",
                'user' => $user,
                'tenant' => tenant(),
                'theme' => Theme::active(),
            ]));

        $status = Password::sendResetLink(['login' => $dto->login]);

        throw_if(
            $status !== Password::RESET_LINK_SENT,
            ValidationException::withMessages(['message' => [__($status)]])
        );

        return __($status);
    }
}
