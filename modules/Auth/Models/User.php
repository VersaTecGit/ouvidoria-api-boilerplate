<?php

declare(strict_types=1);

namespace Modules\Auth\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Auth\Models\Concerns\Impersonation;
use Modules\Common\Core\Models\Concerns\CommonQueries;
use Modules\Common\Core\Models\Concerns\Filterable;
use Modules\Common\Core\Models\Concerns\HasUuids;
use Modules\Common\Core\Models\Concerns\LogChanges;
use Modules\Common\Core\Models\Concerns\UserActions;
use Modules\Common\Core\Models\Media;
use Modules\Transport\Models\VehicleRequest;
use Modules\Transport\Models\VehicleRequestTravelAllowance;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

final class User extends Authenticatable implements HasMedia, JWTSubject
{
    use CascadeSoftDeletes,
        CommonQueries,
        Filterable,
        HasFactory,
        HasRoles,
        HasUuids,
        Impersonation,
        InteractsWithMedia,
        LogChanges,
        Notifiable,
        SoftDeletes,
        UserActions;

    protected $fillable = [
        'uuid',
        'name',
        'login',
        'email',
        'email_verified_at',
        'password',
        'active',
        'first_login',
        'driver',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
        'first_login' => 'boolean',
        'driver' => 'json',
    ];

    protected $attributes = [
        'active' => true,
    ];

    protected $nullable = [
        'driver',
    ];

    protected $cascadeDeletes = [
        'vehicleRequests',
    ];

    public static function nullable(): array
    {
        return (new self())->nullable;
    }

    public static function findByUuid(string $uuid): ?self
    {
        return self::where('uuid', $uuid)->firstOrFail();
    }

    public static function findAllByUuid(string $uuid): ?self
    {
        return self::where('uuid', $uuid)->all()->firstOrFail();
    }

    public function scopeAll(Builder $query): Builder
    {
        return $query->withoutGlobalScope('active-users');
    }

    public function logins(): HasMany
    {
        return $this->hasMany(UserLogin::class);
    }

    public function vehicleRequests(): HasMany
    {
        return $this->hasMany(VehicleRequest::class, 'requester_id');
    }

    public function vehicleRequestsAsDriver(): HasMany
    {
        return $this->hasMany(VehicleRequest::class, 'driver_id');
    }

    public function vehicleRequestPassengers(): MorphToMany
    {
        return $this->morphToMany(VehicleRequest::class, 'vehicle_request_passenger');
    }

    public function travelAllowances(): MorphMany
    {
        return $this->morphMany(VehicleRequestTravelAllowance::class, 'beneficiary');
    }

    public function latestLogin(): HasOne
    {
        return $this->hasOne(UserLogin::class)->latestOfMany();
    }

    public function isFirstLogin(): bool
    {
        return $this->first_login;
    }

    public function markAsNotFirstLogin(): void
    {
        $this->update(['first_login' => false]);
    }

    public function isAdmin(): bool
    {
        return $this->hasPermissionTo('ALL-access-admin-panel');
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('avatars')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this
                    ->addMediaConversion('small')
                    ->width(50)
                    ->height(50);

                $this
                    ->addMediaConversion('medium')
                    ->width(100)
                    ->height(100);

                $this
                    ->addMediaConversion('large')
                    ->width(300)
                    ->height(300);
            });
    }

    public function receivesBroadcastNotificationsOn()
    {
        return 'user.' . $this->uuid;
    }

    protected static function booted(): void
    {
        self::addGlobalScope(
            'active-users',
            fn (Builder $builder) => $builder->where('active', true)
        );
    }
}
