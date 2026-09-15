<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Illuminate\Support\Str;
use Modules\Common\Core\Support\SignedStorageUrlService;
use Modules\Ouvidoria\DTOs\CreatePublicManifestationSignedStorageUrlDTO;
use Symfony\Component\HttpKernel\Exception\HttpException;

final readonly class CreatePublicManifestationSignedStorageUrl
{
    /**
     * Content type => extension written into the storage key.
     *
     * The extension comes from this map, never from the client's file name:
     * the key shape is a contract with the public create endpoint, which only
     * accepts `public-manifestations/<uuid>.(pdf|jpg|png)`.
     */
    public const ALLOWED_CONTENT_TYPES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    public const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024;

    public function __construct(
        private SignedStorageUrlService $signedStorageUrlService,
    ) {}

    public function handle(CreatePublicManifestationSignedStorageUrlDTO $dto): array
    {
        $extension = self::ALLOWED_CONTENT_TYPES[$dto->content_type] ?? null;

        if ($extension === null) {
            throw new HttpException(422, 'Tipo de arquivo não permitido.');
        }

        if ($dto->file_size > self::MAX_FILE_SIZE_BYTES) {
            throw new HttpException(422, 'Arquivo excede o tamanho máximo permitido.');
        }

        $uploadUuid = (string) Str::uuid();
        $key = "public-manifestations/{$uploadUuid}.{$extension}";

        return $this->signedStorageUrlService->generate(
            key: $key,
            contentType: $dto->content_type,
            visibility: 'private',
            uuid: $uploadUuid,
        );
    }
}
