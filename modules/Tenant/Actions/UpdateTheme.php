<?php

declare(strict_types=1);

namespace Modules\Tenant\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Tenant\DTOs\UpdateThemeDTO;
use Modules\Tenant\Exceptions\ThemeSwapException;
use Modules\Tenant\Models\Theme;

final readonly class UpdateTheme
{
    public function __construct(private FetchTheme $fetchTheme) {}

    public function handle(string $uuid, UpdateThemeDTO $dto): Theme
    {
        $theme = $this->fetchTheme->handle($uuid);

        $updateData = $dto->nullableSafeToArray(Theme::nullable());

        try {
            DB::beginTransaction();

            if ($theme->active && $dto->active === false) {
                throw_if(empty($dto->to_activate_id), new ThemeSwapException());
                Theme::enable($dto->to_activate_id);
            }

            if ($dto->primary_logo) {
                $theme->addMediaFromDisk($dto->primary_logo->key, 'central')
                    ->usingFileName($dto->primary_logo->uuid . '.' . $dto->primary_logo->extension)
                    ->toMediaCollection('primary-logos');
            }

            if ($dto->contrast_primary_logo) {
                $theme->addMediaFromDisk($dto->contrast_primary_logo->key, 'central')
                    ->usingFileName($dto->contrast_primary_logo->uuid . '.' . $dto->contrast_primary_logo->extension)
                    ->toMediaCollection('contrast-primary-logos');
            }

            if ($dto->favicon) {
                $theme->addMediaFromDisk($dto->favicon->key, 'central')
                    ->usingFileName($dto->favicon->uuid . '.' . $dto->favicon->extension)
                    ->toMediaCollection('favicons');
            }

            if ($dto->reduced_logo) {
                $theme->addMediaFromDisk($dto->reduced_logo->key, 'central')
                    ->usingFileName($dto->reduced_logo->uuid . '.' . $dto->reduced_logo->extension)
                    ->toMediaCollection('reduced-logos');
            }

            if ($dto->contrast_reduced_logo) {
                $theme->addMediaFromDisk($dto->contrast_reduced_logo->key, 'central')
                    ->usingFileName($dto->contrast_reduced_logo->uuid . '.' . $dto->contrast_reduced_logo->extension)
                    ->toMediaCollection('contrast-reduced-logos');
            }

            $theme->fill($updateData);
            $theme->push();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $theme;
    }
}
