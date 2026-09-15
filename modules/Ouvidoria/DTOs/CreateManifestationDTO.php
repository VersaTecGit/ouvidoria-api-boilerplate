<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\DTOs;

use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\UploadedFileDTO;
use Modules\Ouvidoria\Models\DestinationAgency;
use Modules\Ouvidoria\Models\Unit;
use Modules\Ouvidoria\Rules\CpfOrCnpj;
use Modules\Ouvidoria\Support\ManifestationStatus;
use Modules\Ouvidoria\Support\ManifestationType;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\BooleanCast;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class CreateManifestationDTO extends ValidatedDTO
{
    public ManifestationType $type;

    public ManifestationStatus $status;

    public int $destination_agency_id;

    public ?int $unit_id;

    public string $subject;

    public string $description;

    public string $occurrence_place;

    public bool $is_anonymous;

    public ?string $manifestant_name;

    public ?string $manifestant_email;

    public ?string $manifestant_phone;

    public ?string $manifestant_document;

    public ?UnitAddressDTO $manifestant_address;

    public array $attachments;

    protected function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(ManifestationType::toArray())],
            'destination_agency_id' => ['required', 'string', 'uuid', Rule::exists('destination_agencies', 'uuid')],
            'unit_id' => ['sometimes', 'nullable', 'string', 'uuid', Rule::exists('units', 'uuid')],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'occurrence_place' => ['required', 'string', 'max:255'],
            // Required, not `sometimes`: an omitted flag skips the `required_if` below
            // (ValidatedDTO validates the raw request; `defaults()` runs only after), so
            // it would file an identified manifestation with no contact info. The form
            // always sends the flag explicitly.
            'is_anonymous' => ['required', 'boolean'],

            /*
             * The backend half of the anonymity rule. The frontend hides these
             * fields and nulls them out, but the endpoint is reachable without
             * the form, so identification is enforced here too: required when
             * identifying, dropped entirely when anonymous.
             */
            'manifestant_name' => ['exclude_if:is_anonymous,true', 'required_if:is_anonymous,false', 'nullable', 'string', 'max:255'],
            'manifestant_email' => ['exclude_if:is_anonymous,true', 'required_if:is_anonymous,false', 'nullable', 'email', 'max:255'],
            'manifestant_phone' => ['exclude_if:is_anonymous,true', 'required_if:is_anonymous,false', 'nullable', 'string', 'max:30'],
            'manifestant_document' => ['exclude_if:is_anonymous,true', 'sometimes', 'nullable', 'string', new CpfOrCnpj()],
            'manifestant_address' => ['exclude_if:is_anonymous,true', 'sometimes', 'nullable', 'array'],

            'attachments' => ['sometimes', 'array', 'max:5'],
        ];
    }

    protected function defaults(): array
    {
        return [
            'status' => ManifestationStatus::RECEIVED,
            'is_anonymous' => false,
            'attachments' => [],
        ];
    }

    protected function casts(): array
    {
        return [
            'type' => new EnumCast(ManifestationType::class),
            'status' => new EnumCast(ManifestationStatus::class),
            'destination_agency_id' => fn (string $property, mixed $value) => DestinationAgency::withoutGlobalScope('active-destination-agencies')->where('uuid', $value)->firstOrFail()->id,
            'unit_id' => fn (string $property, mixed $value) => Unit::withoutGlobalScope('active-units')->where('uuid', $value)->firstOrFail()->id,
            'subject' => new StringCast(),
            'description' => new StringCast(),
            'occurrence_place' => new StringCast(),
            'is_anonymous' => new BooleanCast(),
            'manifestant_name' => new StringCast(),
            'manifestant_email' => new StringCast(),
            'manifestant_phone' => new StringCast(),
            'manifestant_document' => new StringCast(),
            'manifestant_address' => new DTOCast(UnitAddressDTO::class),
            'attachments' => new ArrayCast(new DTOCast(UploadedFileDTO::class)),
        ];
    }
}
