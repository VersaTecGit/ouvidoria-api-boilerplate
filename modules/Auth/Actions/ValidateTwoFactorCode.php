<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Modules\Auth\DTOs\ConfirmTwoFactorDTO;
use PragmaRX\Google2FA\Google2FA;

final readonly class ValidateTwoFactorCode
{
    public function __construct(
        private LoggedUser $loggedUser,
        private Google2FA $google2fa
    ) {}

    public function handle(ConfirmTwoFactorDTO $dto): bool
    {
        $user = $this->loggedUser->handle();

        if (! $user->two_factor_secret) {
            return false;
        }

        $secret = decrypt($user->two_factor_secret);

        if ($this->google2fa->verifyKey($secret, $dto->code)) {
            if (is_null($user->two_factor_confirmed_at)) {
                $user->forceFill([
                    'two_factor_confirmed_at' => now(),
                ])->save();
            }

            return true;
        }

        return false;
    }
}
