<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\DTOs;

use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\Utils;
use Modules\Ouvidoria\Models\DestinationAgency;
use Modules\Ouvidoria\Models\Unit;
use Modules\Ouvidoria\Support\ManifestationStatus;
use Modules\Ouvidoria\Support\ManifestationType;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

/**
 * Triage fields only. What the citizen wrote (description, manifestant data,
 * anonymity) is the record of the submission and is not editable by staff;
 * the answer goes through `RespondManifestationDTO` and the timeline.
 */
class UpdateManifestationDTO extends ValidatedDTO
{
    use Utils;

    public ?ManifestationType $type;

    public ?ManifestationStatus $status;

    public ?int $destination_agency_id;

    public ?int $unit_id;

    public ?string $subject;

    public ?string $occurrence_place;

    protected function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', Rule::in(ManifestationType::toArray())],
            'status' => ['sometimes', 'string', Rule::in(ManifestationStatus::toArray())],
            'destination_agency_id' => ['sometimes', 'string', 'uuid', Rule::exists('destination_agencies', 'uuid')],
            'unit_id' => ['sometimes', 'nullable', 'string', 'uuid', Rule::exists('units', 'uuid')],
            'subject' => ['sometimes', 'string', 'max:255'],
            'occurrence_place' => ['sometimes', 'string', 'max:255'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'type' => new EnumCast(ManifestationType::class),
            'status' => new EnumCast(ManifestationStatus::class),
            'destination_agency_id' => fn (string $property, mixed $value) => DestinationAgency::withoutGlobalScope('active-destination-agencies')->where('uuid', $value)->firstOrFail()->id,
            'unit_id' => fn (string $property, mixed $value) => Unit::withoutGlobalScope('active-units')->where('uuid', $value)->firstOrFail()->id,
            'subject' => new StringCast(),
            'occurrence_place' => new StringCast(),
        ];
    }
}
