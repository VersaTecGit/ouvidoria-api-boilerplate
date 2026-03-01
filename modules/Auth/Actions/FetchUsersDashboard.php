<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
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
            'filters' => collect(request()->all())->except('ignore_cache')->toArray(),
        ]));

        if ($dto->ignore_cache) {
            $data = $this->generateDashboardData($dto);
            Cache::put($cacheKey, $data, now()->addMinutes(30));

            return $data;
        }

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            fn () => $this->generateDashboardData($dto)
        );
    }

    protected function getUsersCount(Builder $builder): array
    {
        return $this->getDefaultCount($builder);
    }

    protected function getLastDayLoginsCount(Builder $builder): array
    {
        return $this->getDefaultCount(
            $builder,
            now()->startOfDay(),
            now()->subDay()->startOfDay(),
            now()->startOfDay()
        );
    }

    protected function getUsersCreatedLastMonth(Builder $builder): array
    {
        return $this->getDefaultCount(
            $builder,
            now()->startOfMonth(),
            now()->subMonth()->startOfMonth(),
            now()->startOfMonth()
        );
    }

    protected function getDefaultCount(
        Builder $builder,
        ?Carbon $monthStart = null,
        ?Carbon $previousStart = null,
        ?Carbon $previousEnd = null
    ): array {
        $data = (clone $builder)
            ->selectRaw('
                COUNT(*) as total,
                COUNT(*) FILTER (WHERE created_at >= ?) as month,
                COUNT(*) FILTER (WHERE created_at >= ? AND created_at < ?) as previous
            ', [
                $monthStart ?? now()->startOfMonth(),
                $previousStart ?? now()->subMonth()->startOfMonth(),
                $previousEnd ?? now()->startOfMonth(),
            ])
            ->first();

        return [
            'count' => (int) $data->total,
            'growthRate' => Dashboard::calculateGrowthRate(
                (int) $data->month,
                (int) $data->previous
            ),
        ];
    }

    protected function getLoginsPerMonthChart(Builder $builder): array
    {
        $raw = (clone $builder)
            ->selectRaw("to_char(created_at, 'YYYY-MM') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $data = collect(range(5, 0))->map(function (int $offset) use ($raw) {
            $month = CarbonImmutable::now()->subMonths($offset);

            return [
                'month' => ucfirst($month->locale('pt_BR')->monthName),
                'value' => (int) ($raw[$month->format('Y-m')] ?? 0),
            ];
        })->toArray();

        return ['data' => $data];
    }

    protected function getLastLoginsTable(Builder $builder): array
    {
        $logins = (clone $builder)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $data = $logins->map(fn (UserLogin $login) => [
            'avatar' => $login->user
                ->getFirstTemporaryUrl(Carbon::now()->addHours(2), 'avatars', 'large') ?: null,
            'user' => $login->user->name,
            'login' => $login->created_at->diffForHumans(),
        ])->toArray();

        return ['data' => $data];
    }

    private function generateDashboardData(DashboardDTO $dto): array
    {
        $users = User::query()
            ->filtered($this->filters);

        $users = Dashboard::applyDateRangeFilter(
            $users,
            $dto,
            'users.created_at'
        );

        $logins = UserLogin::query()
            ->whereIn('user_id', (clone $users)->select('id'));

        $logins = Dashboard::applyDateRangeFilter(
            $logins,
            $dto,
            'user_logins.created_at'
        );

        return [
            'data' => [
                'users_count' => $this->getUsersCount($users),
                'last_day_logins_count' => $this->getLastDayLoginsCount($logins),
                'users_created_last_month' => $this->getUsersCreatedLastMonth($users),
                'logins_per_month_chart' => $this->getLoginsPerMonthChart($logins),
                'last_logins_table' => $this->getLastLoginsTable($logins),
            ],
            'updated_at' => now(),
        ];
    }
}
