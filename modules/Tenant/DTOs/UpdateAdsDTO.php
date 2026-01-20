<?php

declare(strict_types=1);

namespace Modules\Tenant\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use Modules\Common\Core\DTOs\Concerns\Utils;
use Modules\Common\Core\DTOs\UploadedFileDTO;
use WendellAdriel\ValidatedDTO\Casting\BooleanCast;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class UpdateAdsDTO extends ValidatedDTO
{
    use Utils;

    public ?string $title;

    public ?string $description;

    public ?UploadedFileDTO $background_image;

    public ?string $button_text;

    public ?string $button_url;

    public ?CarbonImmutable $start_date;

    public ?CarbonImmutable $end_date;

    public ?bool $active;

    protected function rules(): array
    {
        return [
            'title' => ['sometimes', 'nullable', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'background_image' => ['sometimes', 'nullable', 'array'],
            'background_image.extension' => ['sometimes', 'string', Rule::in(['jpeg', 'jpg', 'png'])],
            'button_text' => ['sometimes', 'nullable', 'string'],
            'button_url' => ['sometimes', 'nullable', 'string'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'active' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'background_image' => new DTOCast(UploadedFileDTO::class),
            'start_date' => new CarbonImmutableCast(),
            'end_date' => new CarbonImmutableCast(),
            'active' => new BooleanCast(),
        ];
    }
}
