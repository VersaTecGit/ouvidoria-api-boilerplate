<?php

declare(strict_types=1);

namespace Modules\Tenant\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public function hasModule(string $module): bool
    {
        return $this->modules->contains($module);
    }

    public function versa360Credential(): HasOne
    {
        return $this->hasOne(Versa360Credential::class, 'tenant_id', 'id');
    }

    protected function modules(): Attribute
    {
        return Attribute::make(
            get: fn (?array $value) => collect($value ?? []),
        );
    }
}
