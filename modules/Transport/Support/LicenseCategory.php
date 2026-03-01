<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum LicenseCategory: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case E = 'E';

    public static function all(): array
    {
        return [
            self::A,
            self::B,
            self::C,
            self::D,
            self::E,
        ];
    }

    public static function toArray(): array
    {
        return array_column(LicenseCategory::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::A => 'Categoria A - Motocicletas',
            self::B => 'Categoria B - Automóveis',
            self::C => 'Categoria C - Caminhões',
            self::D => 'Categoria D - Ônibus e Vans',
            self::E => 'Categoria E - Carretas e Articulados',
        };
    }
}
