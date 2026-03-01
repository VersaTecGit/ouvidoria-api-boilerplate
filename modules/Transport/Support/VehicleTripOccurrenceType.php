<?php

declare(strict_types=1);

namespace Modules\Transport\Support;

enum VehicleTripOccurrenceType: string
{
    case HEAVY_TRAFFIC = 'heavy_traffic';
    case ACCIDENT = 'accident';
    case ROAD_WORKS = 'road_works';
    case MANDATORY_DETOUR = 'mandatory_detour';
    case FLOODED_ROAD = 'flooded_road';
    case ROAD_BLOCKED = 'road_blocked';

    case FLAT_TIRE = 'flat_tire';
    case MECHANICAL_FAILURE = 'mechanical_failure';
    case ELECTRICAL_FAILURE = 'electrical_failure';
    case OUT_OF_FUEL = 'out_of_fuel';
    case ENGINE_OVERHEATING = 'engine_overheating';
    case WARNING_LIGHT_ON = 'warning_light_on';

    case NO_PASSENGER_AT_STOP = 'no_passenger_at_stop';
    case PASSENGER_LATE = 'passenger_late';
    case INAPPROPRIATE_BEHAVIOR = 'inappropriate_behavior';
    case MEDICAL_EMERGENCY = 'medical_emergency';
    case PASSENGER_REFUSED_BOARDING = 'passenger_refused_boarding';
    case OBJECT_LEFT_IN_VEHICLE = 'object_left_in_vehicle';

    case ITINERARY_CHANGE = 'itinerary_change';
    case ROUTE_CHANGED_BY_SUPERVISION = 'route_changed_by_supervision';

    case HEAVY_RAIN = 'heavy_rain';
    case FOG = 'fog';
    case FALLEN_TREE = 'fallen_tree';
    case FLOODING = 'flooding';

    case OTHER = 'other';

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
            self::HEAVY_TRAFFIC => 'Congestionamento intenso',
            self::ACCIDENT => 'Acidente na via',
            self::ROAD_WORKS => 'Obras na pista',
            self::MANDATORY_DETOUR => 'Desvio de rota obrigatório',
            self::FLOODED_ROAD => 'Estrada alagada',
            self::ROAD_BLOCKED => 'Interdição total ou parcial da via',

            self::FLAT_TIRE => 'Pneu furado',
            self::MECHANICAL_FAILURE => 'Pane mecânica',
            self::ELECTRICAL_FAILURE => 'Falha no sistema elétrico',
            self::OUT_OF_FUEL => 'Falta de combustível',
            self::ENGINE_OVERHEATING => 'Superaquecimento do motor',
            self::WARNING_LIGHT_ON => 'Luz de alerta no painel',

            self::NO_PASSENGER_AT_STOP => 'Falta de passageiro no ponto',
            self::PASSENGER_LATE => 'Passageiro atrasado',
            self::INAPPROPRIATE_BEHAVIOR => 'Comportamento inadequado',
            self::MEDICAL_EMERGENCY => 'Emergência médica do passageiro',
            self::PASSENGER_REFUSED_BOARDING => 'Passageiro se recusou a embarcar',
            self::OBJECT_LEFT_IN_VEHICLE => 'Esquecimento de objetos no veículo',

            self::ITINERARY_CHANGE => 'Mudança de itinerário',
            self::ROUTE_CHANGED_BY_SUPERVISION => 'Rota alterada por orientação superior',

            self::HEAVY_RAIN => 'Chuva intensa',
            self::FOG => 'Neblina',
            self::FALLEN_TREE => 'Queda de árvore',
            self::FLOODING => 'Alagamentos',

            self::OTHER => 'Outros',
        };
    }
}
