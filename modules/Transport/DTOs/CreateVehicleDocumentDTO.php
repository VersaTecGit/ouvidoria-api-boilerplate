<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\UploadedFileDTO;
use Modules\Transport\Support\VehicleDocumentType;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class CreateVehicleDocumentDTO extends ValidatedDTO
{
    public VehicleDocumentType $type;

    public array $attributes;

    public array $files;

    protected function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:255', Rule::in(VehicleDocumentType::toArray())],
            'attributes' => ['required', 'array'],
            'files' => ['sometimes', 'array'],
            'files.*.extension' => ['required', 'string', Rule::in(['jpeg', 'jpg', 'png', 'pdf'])],
        ];
    }

    protected function defaults(): array
    {
        return [
            'files' => [],
        ];
    }

    protected function casts(): array
    {
        return [
            'type' => new EnumCast(VehicleDocumentType::class),
            'attributes' => new ArrayCast(),
            'files' => new ArrayCast(new DTOCast(UploadedFileDTO::class)),
        ];
    }
}
