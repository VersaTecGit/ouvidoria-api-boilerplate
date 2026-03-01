<?php

declare(strict_types=1);

namespace Modules\Questionnaires\DTOs;

use Illuminate\Validation\Rule;
use Modules\Questionnaires\Support\QuestionnaireElementType;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class QuestionnaireElementDTO extends ValidatedDTO
{
    public string $id;

    public string $type;

    public array $extraAttributes;

    public int $row;

    public int $col;

    protected function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'type' => ['required', 'string', Rule::in(QuestionnaireElementType::all())],
            'extraAttributes' => ['sometimes', 'array'],
            'row' => ['required', 'integer'],
            'col' => ['required', 'integer'],
        ];
    }

    protected function defaults(): array
    {
        return [
            'extraAttributes' => [],
        ];
    }

    protected function casts(): array
    {
        return [
            'id' => new StringCast(),
            'type' => new StringCast(),
            'extraAttributes' => new ArrayCast(),
            'row' => new IntegerCast(),
            'col' => new IntegerCast(),
        ];
    }
}
