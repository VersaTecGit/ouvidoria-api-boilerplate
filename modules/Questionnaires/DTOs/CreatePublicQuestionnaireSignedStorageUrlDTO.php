<?php

declare(strict_types=1);

namespace Modules\Questionnaires\DTOs;

use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

final class CreatePublicQuestionnaireSignedStorageUrlDTO extends ValidatedDTO
{
    public string $field_id;

    public string $content_type;

    public string $file_name;

    public int $file_size;

    protected function rules(): array
    {
        return [
            'field_id' => ['required', 'string'],
            'content_type' => ['required', 'string'],
            'file_name' => ['required', 'string'],
            'file_size' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'field_id' => new StringCast(),
            'content_type' => new StringCast(),
            'file_name' => new StringCast(),
            'file_size' => new IntegerCast(),
        ];
    }
}
