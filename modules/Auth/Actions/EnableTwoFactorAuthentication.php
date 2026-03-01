<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

final readonly class EnableTwoFactorAuthentication
{
    public function __construct(
        private LoggedUser $loggedUser,
        private Google2FA $google2fa
    ) {}

    public function handle(): array
    {
        $user = $this->loggedUser->handle();
        $secretKey = $this->google2fa->generateSecretKey();

        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = Str::random(10) . '-' . Str::random(10);
        }

        $user->forceFill([
            'two_factor_secret' => encrypt($secretKey),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => null,
        ])->save();

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );

        return [
            'secret' => $secretKey,
            'qr_code_url' => $qrCodeUrl,
            'recovery_codes' => $recoveryCodes,
        ];
    }
}
