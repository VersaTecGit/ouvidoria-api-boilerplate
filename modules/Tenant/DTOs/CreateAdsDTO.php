<?php

declare(strict_types=1);

namespace Modules\Tenant\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\CarbonImmutableCast;
use Modules\Common\Core\DTOs\UploadedFileDTO;
use Modules\Tenant\Models\Ads;
use WendellAdriel\ValidatedDTO\Casting\BooleanCast;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class CreateAdsDTO extends ValidatedDTO
{
    public ?string $title;

    public ?string $description;

    public ?UploadedFileDTO $background_image;

    public ?string $button_text;

    public ?string $button_url;

    public ?CarbonImmutable $start_date;

    public ?CarbonImmutable $end_date;

    public bool $active;

    protected function rules(): array
    {
        return [
            'title' => ['required_without:background_image', 'nullable', 'string'],
            'description' => ['nullable', 'string'],
            'background_image' => ['required_without:title', 'array'],
            'background_image.extension' => ['sometimes', 'string', Rule::in(['jpeg', 'jpg', 'png'])],
            'button_text' => ['nullable', 'string'],
            'button_url' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    protected function defaults(): array
    {
        return [
            'order' => Ads::max('order') + 1,
            'active' => true,
        ];
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
