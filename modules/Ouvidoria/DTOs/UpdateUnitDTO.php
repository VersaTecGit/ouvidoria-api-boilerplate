<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\DTOs;

use Illuminate\Validation\Rule;
use Modules\Common\Core\DTOs\Concerns\Rules\Cnpj;
use Modules\Common\Core\DTOs\Concerns\Utils;
use Modules\Ouvidoria\Models\UnitType;
use WendellAdriel\ValidatedDTO\Casting\ArrayCast;
use WendellAdriel\ValidatedDTO\Casting\BooleanCast;
use WendellAdriel\ValidatedDTO\Casting\DTOCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class UpdateUnitDTO extends ValidatedDTO
{
    use Utils;

    public ?string $name;

    public ?string $description;

    public ?bool $active;

    public ?string $code;

    public ?int $unit_type_id;

    public ?string $cnpj;

    public ?UnitAddressDTO $address;

    public ?array $contacts;

    public ?string $open_time;

    public ?string $close_time;

    protected function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
            'code' => ['sometimes', 'string', 'max:50'],
            'unit_type_id' => ['sometimes', 'string', 'uuid', Rule::exists('unit_types', 'uuid')],
            'cnpj' => ['sometimes', 'nullable', 'string', new Cnpj()],
            'address' => ['sometimes', 'nullable', 'array'],
            'contacts' => ['sometimes', 'nullable', 'array'],
            'open_time' => ['sometimes', 'nullable', 'date_format:H:i'],
            'close_time' => ['sometimes', 'nullable', 'date_format:H:i'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'name' => new StringCast(),
            'description' => new StringCast(),
            'active' => new BooleanCast(),
            'code' => new StringCast(),
            'unit_type_id' => fn (string $property, mixed $value) => UnitType::findByUuid($value)->id,
            'cnpj' => new StringCast(),
            'address' => new DTOCast(UnitAddressDTO::class),
            'contacts' => new ArrayCast(),
            'open_time' => new StringCast(),
            'close_time' => new StringCast(),
        ];
    }
}
