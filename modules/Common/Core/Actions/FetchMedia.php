<?php

declare(strict_types=1);

namespace Modules\Common\Core\Actions;

use Modules\Common\Core\Models\Media;

final readonly class FetchMedia
{
    public function handle(string $uuid): Media
    {
        return Media::with('model')->findAllByUuid($uuid);
    }
}
