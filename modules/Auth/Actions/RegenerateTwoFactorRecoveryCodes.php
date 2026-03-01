<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Illuminate\Support\Str;

final readonly class RegenerateTwoFactorRecoveryCodes
{
    public function __construct(
        private LoggedUser $loggedUser,
    ) {}

    public function handle(): array
    {
        $user = $this->loggedUser->handle();

        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = Str::random(10) . '-' . Str::random(10);
        }

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ])->save();

        return $recoveryCodes;
    }
}
