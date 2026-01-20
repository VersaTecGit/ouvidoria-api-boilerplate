<?php

declare(strict_types=1);

namespace Modules\Common\Core\DTOs\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

trait Utils
{
    public function nullableSafeToArray(array $nullable = []): array
    {
        return collect($this->toArray())
            ->filter(
                fn (mixed $item, string $key) => ! is_null($item) || in_array($key, $nullable, true) && is_array($this->data) && Arr::exists($this->data, $key)
            )
            ->toArray();
    }

    public function toMultipart(): array
    {
        $multipart = [];

        foreach ($this->toArray() as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $file) {
                    if ($file instanceof UploadedFile) {
                        $multipart[] = [
                            'name' => $key . '[]',
                            'contents' => fopen($file->getPathname(), 'r'),
                            'filename' => $file->getClientOriginalName(),
                        ];
                    }
                }
            } elseif ($value instanceof UploadedFile) {
                $multipart[] = [
                    'name' => $key,
                    'contents' => fopen($value->getPathname(), 'r'),
                    'filename' => $value->getClientOriginalName(),
                ];
            } else {
                $multipart[] = [
                    'name' => $key,
                    'contents' => $value,
                ];
            }
        }

        return $multipart;
    }
}
