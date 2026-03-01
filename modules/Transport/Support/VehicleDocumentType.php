<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

use Modules\Transport\DTOs\InsuranceDocumentDTO;
use Modules\Transport\DTOs\IpvaDocumentDTO;
use Modules\Transport\DTOs\LicensingDocumentDTO;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

enum VehicleDocumentType: string
{
    case IPVA = 'ipva';
    case LICENSING = 'licensing';
    case INSURANCE = 'insurance';

    public static function all(): array
    {
        return [
            self::IPVA,
            self::LICENSING,
            self::INSURANCE,
        ];
    }

    public static function toArray(): array
    {
        return array_column(VehicleDocumentType::cases(), 'value');
    }

    public function dto(): ValidatedDTO
    {
        return match ($this) {
            self::IPVA => new IpvaDocumentDTO(),
            self::LICENSING => new LicensingDocumentDTO(),
            self::INSURANCE => new InsuranceDocumentDTO(),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::IPVA => 'IPVA',
            self::LICENSING => 'Licenciamento Anual de Veículos',
            self::INSURANCE => 'Seguro Obrigatório (DPVAT)',
        };
    }
}
