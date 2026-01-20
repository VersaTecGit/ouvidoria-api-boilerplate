<?php

declare(strict_types=1);

namespace Modules\Common\Core\DTOs;

use Carbon\CarbonImmutable;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use Modules\Common\Core\Support\Formatter;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class DashboardDTO extends ValidatedDTO
{
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
            'start_date' => ['sometimes', 'string', 'date_format:' . Formatter::API_DATE_FORMAT],
            'end_date' => ['sometimes', 'string', 'date_format:' . Formatter::API_DATE_FORMAT],
        ];
    }

    protected function defaults(): array
    {
        return [
            'end_date' => date(Formatter::API_DATE_FORMAT),
        ];
    }

    protected function casts(): array
    {
        return [
            'start_date' => new CarbonImmutableCast(null, Formatter::API_DATE_FORMAT),
            'end_date' => new CarbonImmutableCast(null, Formatter::API_DATE_FORMAT),
        ];
    }
}
