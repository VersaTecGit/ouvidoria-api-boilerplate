<?php

declare(strict_types=1);

namespace Modules\Common\Core\DTOs;

use Carbon\CarbonImmutable;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use Modules\Common\Core\Support\Formatter;
use WendellAdriel\ValidatedDTO\Casting\BooleanCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class DashboardDTO extends ValidatedDTO
{
    public bool $ignore_cache;

    public ?CarbonImmutable $start_date;

    public CarbonImmutable $end_date;

    public function defineTimeframe(string $timeframe): self
    {
        if (! is_null($this->start_date)) {
            return $this;
        }

        $this->start_date = CarbonImmutable::now()->sub($timeframe)->startOfDay();
        $this->end_date = CarbonImmutable::now();

        return $this;
    }

    protected function rules(): array
    {
        return [
            'ignore_cache' => ['sometimes', 'string', 'in:true,false'],
            'start_date' => ['sometimes', 'string', 'date_format:' . Formatter::API_DATE_FORMAT],
            'end_date' => ['sometimes', 'string', 'date_format:' . Formatter::API_DATE_FORMAT],
        ];
    }

    protected function defaults(): array
    {
        return [
            'ignore_cache' => false,
        ];
    }

    protected function casts(): array
    {
        return [
            'ignore_cache' => new BooleanCast(),
            'start_date' => new CarbonImmutableCast(null, Formatter::API_DATE_FORMAT),
            'end_date' => new CarbonImmutableCast(null, Formatter::API_DATE_FORMAT),
        ];
    }
}
