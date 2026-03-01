<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\LoginWithTwoFactorDTO;
use Modules\Auth\Models\User;
use PragmaRX\Google2FA\Google2FA;

final readonly class LoginWithTwoFactor
{
    private const TOKEN_TYPE = 'Bearer';

    public function __construct(
        private Google2FA $google2fa
    ) {}

    public function handle(LoginWithTwoFactorDTO $dto): array
    {
        $user = User::findByUuid($dto->uuid);

        if (! $user || ! $user->two_factor_secret) {
            throw ValidationException::withMessages([
                'code' => __('Invalid request.'),
            ]);
        }

        $decryptedSecret = decrypt($user->two_factor_secret);
        $isValid = $this->google2fa->verifyKey($decryptedSecret, $dto->code);

        if (! $isValid) {
            $isValid = $this->useRecoveryCode($user, $dto->code);
        }

        if (! $isValid) {
            throw ValidationException::withMessages([
                'code' => __('O código de autenticação está inválido.'),
            ]);
        }

        $token = Auth::guard('api')->login($user);

        return [
            'type' => self::TOKEN_TYPE,
            'token' => $token,
        ];
    }

    private function useRecoveryCode(User $user, string $code): bool
    {
        if (! $user->two_factor_recovery_codes) {
            return false;
        }

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        if (! is_array($recoveryCodes)) {
            return false;
        }

        foreach ($recoveryCodes as $key => $recoveryCode) {
            if ($recoveryCode === $code) {
                unset($recoveryCodes[$key]);

                $user->forceFill([
                    'two_factor_recovery_codes' => encrypt(json_encode(array_values($recoveryCodes))),
                ])->save();

                return true;
            }
        }

        return false;
    }
}
