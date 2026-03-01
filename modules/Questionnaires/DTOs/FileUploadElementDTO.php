<?php

declare(strict_types=1);

namespace Modules\Questionnaires\DTOs;

use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\UploadedFileDTO;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class FileUploadElementDTO extends ValidatedDTO
{
    public string $fileName;

    public UploadedFileDTO $file;

    protected function rules(): array
    {
        return [
            'fileName' => ['required', 'string'],
            'file' => ['required', 'array'],
            'file.extension' => ['required', 'string', Rule::in(config('file-extensions.allowed'))],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'fileName' => new StringCast(),
            'file' => new DTOCast(UploadedFileDTO::class),
        ];
    }
}
