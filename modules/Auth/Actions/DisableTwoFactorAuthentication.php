<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

final readonly class DisableTwoFactorAuthentication
{
    public function __construct(
        private LoggedUser $loggedUser,
    ) {}

    public function handle(): void
    {
        $user = $this->loggedUser->handle();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }
}
