<?php

declare(strict_types=1);

namespace Modules\Common\Core\DTOs\Concerns;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\Config;
use Throwable;
use WendellAdriel\ValidatedDTO\Casting\Castable;
use WendellAdriel\ValidatedDTO\Exceptions\CastException;

final class CarbonImmutableCast implements Castable
{
    public function __construct(
        private ?string $timezone = null,
        private ?string $format = null
    ) {}

    /**
     * @throws CastException
     */
    public function cast(string $property, mixed $value): CarbonImmutable
    {
        try {
            if ($value instanceof CarbonImmutable) {
                return $value;
            }

            if ($value instanceof DateTimeInterface) {
                return CarbonImmutable::instance($value);
            }

            if ($value instanceof DateTimeInterface) {
                return CarbonImmutable::instance($value)->utc();
            }

            $timezone = $this->timezone ?? Config::get('app.timezone');

            $date = is_null($this->format)
                ? CarbonImmutable::parse($value, $timezone)
                : CarbonImmutable::createFromFormat($this->format, $value, $timezone);

            return $date->utc();
        } catch (Throwable) {
            throw new CastException($property);
        }
    }
}
