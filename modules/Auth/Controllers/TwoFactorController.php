<?php

declare(strict_types=1);

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Actions\DisableTwoFactorAuthentication;
use Modules\Auth\Actions\EnableTwoFactorAuthentication;
use Modules\Auth\Actions\RegenerateTwoFactorRecoveryCodes;
use Modules\Auth\Actions\ValidateTwoFactorCode;
use Modules\Auth\DTOs\ConfirmTwoFactorDTO;
use Modules\Auth\Resources\RecoveryCodesResource;
use Modules\Auth\Resources\TwoFactorEnabledResource;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;

final class TwoFactorController extends Controller
{
    public function store(EnableTwoFactorAuthentication $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new TwoFactorEnabledResource($action->handle())
        );
    }

    public function confirm(ConfirmTwoFactorDTO $dto, ValidateTwoFactorCode $action): NoContentResponse
    {
        $isValid = $action->handle($dto);

        if (! $isValid) {
            throw ValidationException::withMessages([
                'code' => __('O código de autenticação está inválido.'),
            ]);
        }

        return new NoContentResponse();
    }

    public function destroy(DisableTwoFactorAuthentication $action): NoContentResponse
    {
        $action->handle();

        return new NoContentResponse();
    }

    public function regenerate(RegenerateTwoFactorRecoveryCodes $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(
            new RecoveryCodesResource($action->handle())
        );
    }
}
