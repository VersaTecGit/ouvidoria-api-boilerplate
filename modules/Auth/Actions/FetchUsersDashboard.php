<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Filters\UserFilters;
use Modules\Auth\Models\User;
use Modules\Auth\Models\UserLogin;
use Modules\Common\Core\DTOs\DashboardDTO;
use Modules\Common\Core\Support\Dashboard;

final readonly class FetchUsersDashboard
{
    public function __construct(private UserFilters $filters) {}

    public function handle(DashboardDTO $dto): array
    {
        $cacheKey = 'dashboard_users_' . md5(json_encode([
            'start_date' => $dto->start_date ?? null,
            'end_date' => $dto->end_date ?? null,
            'filters' => collect(request()->all())->except('ignore_cache')->toArray()
        ]));

        if ($dto->ignore_cache) {
            $data = $this->generateDashboardData($dto);
            Cache::put($cacheKey, $data, now()->addMinutes(30));

            return $data;
        }

        return Cache::remember($cacheKey, now()->addMinutes(30), fn () => $this->generateDashboardData($dto));
    }

    protected function getUsersCount(Builder $builder): array
    {
        $count = (clone $builder)->count();
        $previous = User::query()->where('created_at', '<', now()->startOfMonth())->count();
        $growthRate = Dashboard::calculateGrowthRate($count, $previous);

        return [
            'count' => $count,
            'growthRate' => $growthRate,
        ];
    }

    protected function getLastDayLoginsCount(Builder $builder): array
    {
        $count = (clone $builder)->where('created_at', '>=', now()->startOfDay())->count();
        $previous = (clone $builder)->where('created_at', '>=', now()->subDay()->startOfDay())->where('created_at', '<', now()->startOfDay())->count();

        $growthRate = Dashboard::calculateGrowthRate($count, $previous);

        return [
            'count' => $count,
            'growthRate' => $growthRate,
        ];
    }

    protected function getUsersCreatedLastMonth(Builder $builder): array
    {
        $count = (clone $builder)->where('created_at', '>=', now()->startOfMonth())->count();
        $previous = (clone $builder)->where('created_at', '>=', now()->subMonth()->startOfMonth())->where('created_at', '<', now()->startOfMonth())->count();

        $growthRate = Dashboard::calculateGrowthRate($count, $previous);

        return [
            'count' => $count,
            'growthRate' => $growthRate,
        ];
    }

    protected function getLoginsPerMonthChart(Builder $builder): array
    {
        $loginsPerMonth = (clone $builder)
            ->selectRaw('to_char(created_at, \'YYYY-MM\') as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(6)
            ->get()
            ->select('total', 'month');

        $loginsPerMonthKeyed = $loginsPerMonth->pluck('total', 'month');

        $data = collect(range(0, 5))->map(function (int $index) use ($loginsPerMonthKeyed) {
            $month = CarbonImmutable::now()->subMonths($index);

            return [
                'month' => ucfirst($month->locale('pt_BR')->monthName),
                'value' => $loginsPerMonthKeyed->get($month->format('Y-m')) ?? 0,
            ];
        })->reverse()->toArray();

        return [
            'data' => $data,
        ];
    }

    protected function getLastLoginsTable(Builder $builder): array
    {
        $logins = (clone $builder)->orderByDesc('created_at')->limit(5)->get();

        $data = $logins->map(fn (UserLogin $login) => [
            'avatar' => $login->user->getFirstTemporaryUrl(Carbon::now()->addHours(2), 'avatars', 'large') ?: null,
            'user' => $login->user->name,
            'login' => $login->created_at->diffForHumans(),
        ])->toArray();

        return [
            'data' => $data,
        ];
    }

    private function generateDashboardData(DashboardDTO $dto): array
    {
        DB::beginTransaction();
        try {
            $usersQuery = User::query()->filtered($this->filters)->all();
            $usersQuery = Dashboard::applyDateRangeFilter($usersQuery, $dto);

            $usersLoginQuery = UserLogin::query()
                ->whereIn('user_id', function (QueryBuilder $query) use ($usersQuery) {
                    $query->select('id')->fromSub($usersQuery, 'sub_users');
                });

            $usersLoginQuery = Dashboard::applyDateRangeFilter($usersLoginQuery, $dto);

            $data = [
                'users_count' => $this->getUsersCount(clone $usersQuery),
                'last_day_logins_count' => $this->getLastDayLoginsCount(clone $usersLoginQuery),
                'users_created_last_month' => $this->getUsersCreatedLastMonth(clone $usersQuery),
                'logins_per_month_chart' => $this->getLoginsPerMonthChart(clone $usersLoginQuery),
                'last_logins_table' => $this->getLastLoginsTable(clone $usersLoginQuery),
            ];

            DB::commit();

            return [
                'data' => $data,
                'updated_at' => now(),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
