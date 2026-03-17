<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Modules\Questionnaires\DTOs\CreatePublicQuestionnaireSignedStorageUrlDTO;
use Modules\Questionnaires\Models\Questionnaire;
use Modules\Questionnaires\Support\PublicQuestionnaireSignedStorageUrlService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class CreatePublicQuestionnaireSignedStorageUrl
{
    public function __construct(
        private PublicQuestionnaireSignedStorageUrlService $signedStorageUrlService,
    ) {}

    public function handle(string $uuid, CreatePublicQuestionnaireSignedStorageUrlDTO $dto): array
    {
        $questionnaire = Questionnaire::query()
            ->where('uuid', $uuid)
            ->first();

        if (! $questionnaire || ! $questionnaire->active) {
            throw new NotFoundHttpException();
        }

        $field = collect($questionnaire->elements)
            ->first(
                fn (array $element): bool => Arr::get($element, 'id') === $dto->field_id
                && Arr::get($element, 'type') === 'FileUploadField'
            );

        if (! $field) {
            throw new HttpException(422, 'Campo de upload inválido.');
        }

        $allowedMimeTypes = Arr::get($field, 'extraAttributes.allowedMimeTypes', [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ]);

        $maxFileSizeMb = (int) Arr::get($field, 'extraAttributes.maxFileSizeMb', 10);
        $maxFileSizeBytes = $maxFileSizeMb * 1024 * 1024;

        if (! in_array($dto->content_type, $allowedMimeTypes, true)) {
            throw new HttpException(422, 'Tipo de arquivo não permitido.');
        }

        if ($dto->file_size > $maxFileSizeBytes) {
            throw new HttpException(422, 'Arquivo excede o tamanho máximo permitido.');
        }

        $uploadUuid = (string) Str::uuid();
        $extension = pathinfo($dto->file_name, PATHINFO_EXTENSION);

        $key = sprintf(
            'public-questionnaires/%s/%s.%s',
            $questionnaire->id,
            $uploadUuid,
            $extension ?: 'bin',
        );

        return $this->signedStorageUrlService->generate(
            key: $key,
            contentType: $dto->content_type,
            visibility: 'private',
            uuid: $uploadUuid,
        );
    }
}
