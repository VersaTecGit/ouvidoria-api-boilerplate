<?php

declare(strict_types=1);

namespace Tests\Feature\Ouvidoria\Helpers;

use Modules\Ouvidoria\Models\Manifestation;
use Modules\Ouvidoria\Models\ManifestationLog;
use Modules\Ouvidoria\Support\ManifestationStatus;
use Modules\Ouvidoria\Support\ManifestationType;

class ManifestationsHelper
{
    public static function createTestManifestation(array $overrides = []): Manifestation
    {
        $agencyId = $overrides['destination_agency_id']
            ?? DestinationAgenciesHelper::createTestDestinationAgency()->id;

        $manifestation = new Manifestation([
            'protocol_number' => Manifestation::generateProtocolNumber(),
            'type' => ManifestationType::COMPLAINT,
            'status' => ManifestationStatus::RECEIVED,
            'destination_agency_id' => $agencyId,
            'unit_id' => null,
            'subject' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'occurrence_place' => fake()->streetAddress(),
            'is_anonymous' => true,
            ...$overrides,
        ]);

        $manifestation->save();

        return $manifestation;
    }

    public static function createTestLog(Manifestation $manifestation, array $overrides = []): ManifestationLog
    {
        $log = new ManifestationLog([
            'manifestation_id' => $manifestation->id,
            'content' => fake()->sentence(),
            'is_public' => false,
            'status' => null,
            'author_id' => null,
            ...$overrides,
        ]);

        // Timestamps are not fillable; set them directly so a test can control the timeline order.
        foreach (['created_at', 'updated_at'] as $timestamp) {
            if (array_key_exists($timestamp, $overrides)) {
                $log->{$timestamp} = $overrides[$timestamp];
            }
        }

        $log->save();

        return $log;
    }

    /**
     * A complete, identified public payload; override anything to break it.
     */
    public static function dumbPublicManifestationData(string $agencyUuid, array $overrides = []): array
    {
        return [
            'type' => ManifestationType::COMPLAINT->value,
            'destination_agency_id' => $agencyUuid,
            'subject' => 'Buraco na rua',
            'description' => 'Existe um buraco grande na frente do número 100.',
            'occurrence_place' => 'Rua das Flores, 100',
            'is_anonymous' => false,
            'manifestant_name' => 'Maria da Silva',
            'manifestant_email' => 'maria@example.com',
            'manifestant_phone' => '(11) 99999-0000',
            'manifestant_document' => '529.982.247-25',
            ...$overrides,
        ];
    }
}
