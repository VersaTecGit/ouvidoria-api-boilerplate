<?php

declare(strict_types=1);

namespace Modules\Questionnaires\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class CreateQuestionnaireResponseDTO extends ValidatedDTO
{
    public string $questionnaire_id;

    public array $answers;

    public ?CarbonImmutable $started_at;

    public ?CarbonImmutable $ended_at;

    protected function rules(): array
    {
        return [
            'questionnaire_id' => ['required', 'string', Rule::exists('questionnaires', 'uuid')],
            'answers' => ['sometimes', 'array'],
            'started_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    protected function defaults(): array
    {
        return [
            'answers' => [],
            'started_at' => now(),
            'ended_at' => now(),
        ];
    }

    protected function casts(): array
    {
        return [
            'questionnaire_id' => new StringCast(),
            'answers' => new ArrayCast(new StringCast()),
            'started_at' => new CarbonImmutableCast(),
            'ended_at' => new CarbonImmutableCast(),
        ];
    }
}
