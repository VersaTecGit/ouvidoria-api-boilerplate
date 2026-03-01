<?php

declare(strict_types=1);

namespace Modules\Common\Core\Support;

enum WeekDay: string
{
    case MONDAY = 'monday';
    case TUESDAY = 'tuesday';
    case WEDNESDAY = 'wednesday';
    case THURSDAY = 'thursday';
    case FRIDAY = 'friday';
    case SATURDAY = 'saturday';
    case SUNDAY = 'sunday';

    public static function toArray(): array
    {
        return array_column(WeekDay::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::MONDAY => 'Segunda-feira',
            self::TUESDAY => 'Terça-feira',
            self::WEDNESDAY => 'Quarta-feira',
            self::THURSDAY => 'Quinta-feira',
            self::FRIDAY => 'Sexta-feira',
            self::SATURDAY => 'Sábado',
            self::SUNDAY => 'Domingo',
        };
    }
}
