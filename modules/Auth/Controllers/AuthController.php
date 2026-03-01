<?php

declare(strict_types=1);

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\Actions\LoggedUser;
use Modules\Auth\Actions\Login;
use Modules\Auth\Actions\LoginWithTwoFactor;
use Modules\Auth\Actions\Refresh;
use Modules\Auth\DTOs\LoginDTO;
use Modules\Auth\DTOs\LoginWithTwoFactorDTO;
use Modules\Auth\Resources\LoginResource;
use Modules\Auth\Resources\LoginWithTwoFactorResource;
use Modules\Auth\Resources\TokenResource;
use Modules\Auth\Resources\UserResource;
use Modules\Common\Core\Responses\ApiSuccessResponse;
use Modules\Common\Core\Responses\NoContentResponse;
use Modules\Common\Core\Support\Modules;
use Modules\Common\Logs\Support\AccessActions;
use Modules\Common\Logs\Support\AccessLogHelper;

final class AuthController extends Controller
{
    public function login(LoginDTO $dto, Login $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new LoginResource($action->handle($dto)));
    }

    public function loginWithTwoFactor(LoginWithTwoFactorDTO $dto, LoginWithTwoFactor $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new LoginWithTwoFactorResource($action->handle($dto)));
    }

    public function refresh(Refresh $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new TokenResource($action->handle()));
    }

    public function logout(): NoContentResponse
    {
        AccessLogHelper::log(action: AccessActions::LOGOUT, module: Modules::AUTH);
        Auth::logout(true);

        return new NoContentResponse();
    }

    public function user(LoggedUser $action): ApiSuccessResponse
    {
        return new ApiSuccessResponse(new UserResource($action->handle()));
    }
}
