<?php

declare(strict_types=1);

namespace Modules\Common;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Auth\Models\User;

class CommonServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->bootSignedStorage();
    }

    private function bootSignedStorage(): void
    {
        Gate::define('uploadFiles', function (?User $user, ?string $bucket): bool {
            if (request()->attributes->get('is_microservice') === true) {
                return true;
            }

            if ($user instanceof User) {
                return true;
            }

            return false;
        });
    }
}
