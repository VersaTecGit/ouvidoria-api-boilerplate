<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Support;

enum ManifestationStatus: string
{
    case RECEIVED = 'recebida';
    case UNDER_REVIEW = 'em_analise';
    case ANSWERED = 'respondida';
    case ARCHIVED = 'arquivada';

    public static function all(): array
    {
        return self::cases();
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::RECEIVED => 'Recebida',
            self::UNDER_REVIEW => 'Em análise',
            self::ANSWERED => 'Respondida',
            self::ARCHIVED => 'Arquivada',
        };
    }
}
