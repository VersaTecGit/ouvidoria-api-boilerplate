<?php

declare(strict_types=1);

namespace Modules\Transport\DTOs;

use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\Utils;
use Modules\Common\Core\DTOs\UploadedFileDTO;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class UpdateVehicleDocumentDTO extends ValidatedDTO
{
    use Utils;

    public ?array $attributes;

    public ?array $files_to_remove;

    public ?array $files;

    protected function rules(): array
    {
        return [
            'attributes' => ['sometimes', 'array'],
            'files' => ['sometimes', 'array'],
            'files_to_remove' => ['sometimes', 'array'],
            'files.*.extension' => ['required', 'string', Rule::in(['jpeg', 'jpg', 'png', 'pdf'])],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'attributes' => new ArrayCast(),
            'files_to_remove' => new ArrayCast(),
            'files' => new ArrayCast(new DTOCast(UploadedFileDTO::class)),
        ];
    }
}
