<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Support;

enum ManifestationType: string
{
    case COMPLAINT = 'reclamacao';
    case REPORT = 'denuncia';
    case COMPLIMENT = 'elogio';
    case SUGGESTION = 'sugestao';
    case REQUEST = 'solicitacao';
    case CRITICISM = 'critica';

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
            self::COMPLAINT => 'Reclamação',
            self::REPORT => 'Denúncia',
            self::COMPLIMENT => 'Elogio',
            self::SUGGESTION => 'Sugestão',
            self::REQUEST => 'Solicitação',
            self::CRITICISM => 'Crítica',
        };
    }
}
