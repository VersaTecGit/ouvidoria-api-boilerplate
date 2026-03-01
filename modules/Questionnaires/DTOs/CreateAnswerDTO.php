<?php

declare(strict_types=1);

namespace Modules\Questionnaires\DTOs;

use Illuminate\Validation\Rule;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class CreateAnswerDTO extends ValidatedDTO
{
    public string $questionnaire_id;

    public string $questionnaires_group_id;

    public int $version;

    public array $content;

    public string $justification;

    public string $status;

    protected function rules(): array
    {
        return [
            'questionnaire_id' => ['required', 'string', Rule::exists('questionnaires', 'uuid')],
            'questionnaires_group_id' => ['required', 'string', Rule::exists('questionnaires_groups', 'uuid')],
            'version' => ['required', 'integer'],
            'content' => ['sometimes', 'array'],
            'justification' => ['sometimes', 'string'],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'completed'])],
        ];
    }

    protected function defaults(): array
    {
        return [
            'content' => [],
            'justification' => '',
            'status' => 'pending',
        ];
    }

    protected function casts(): array
    {
        return [
            'questionnaire_id' => new StringCast(),
            'questionnaires_group_id' => new StringCast(),
            'version' => new IntegerCast(),
            'content' => new ArrayCast(),
            'justification' => new StringCast(),
            'status' => new StringCast(),
        ];
    }
}
