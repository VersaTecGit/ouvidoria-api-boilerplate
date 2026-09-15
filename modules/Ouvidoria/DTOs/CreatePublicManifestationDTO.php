<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\DTOs;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * The anonymous citizen's create payload. Same shape as the staff DTO — the
 * anonymity, document and agency rules all carry over — but attachments are
 * hardened: an unauthenticated caller must not be able to point
 * `addMediaFromDisk` at an arbitrary object of the `central` disk, so each
 * attachment must be exactly one the public signed-URL endpoint (Fase 5)
 * could have issued: under `public-manifestations/`, named by its own UUID,
 * with an allow-listed extension, in our own bucket.
 */
class CreatePublicManifestationDTO extends CreateManifestationDTO
{
    private const array ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'png'];

    private const string KEY_PATTERN = '/^public-manifestations\/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})\.(pdf|jpg|png)$/';

    protected function rules(): array
    {
        return [
            ...parent::rules(),
            'attachments.*.uuid' => ['required', 'string', 'uuid'],
            'attachments.*.bucket' => ['required', 'string', Rule::in([config('filesystems.disks.central.bucket')])],
            'attachments.*.key' => ['required', 'string', 'regex:' . self::KEY_PATTERN],
            'attachments.*.extension' => ['required', 'string', Rule::in(self::ALLOWED_EXTENSIONS)],
        ];
    }

    /**
     * Cross-field check: the `uuid` and `extension` declared alongside a key
     * must be the ones embedded in the key itself, so the stored file name
     * (`uuid.extension`) always names the object that was actually uploaded.
     */
    protected function after(Validator $validator): void
    {
        $attachments = $validator->getData()['attachments'] ?? [];

        if (! is_array($attachments)) {
            return;
        }

        foreach ($attachments as $index => $attachment) {
            if (! is_array($attachment) || ! is_string($attachment['key'] ?? null)) {
                continue;
            }

            // The regex rule already reported an ill-shaped key; nothing to compare against.
            if (preg_match(self::KEY_PATTERN, $attachment['key'], $matches) !== 1) {
                continue;
            }

            [, $keyUuid, $keyExtension] = $matches;

            if (($attachment['uuid'] ?? null) !== $keyUuid) {
                $validator->errors()->add(
                    "attachments.{$index}.uuid",
                    'O uuid do anexo não corresponde ao arquivo enviado.'
                );
            }

            if (($attachment['extension'] ?? null) !== $keyExtension) {
                $validator->errors()->add(
                    "attachments.{$index}.extension",
                    'A extensão do anexo não corresponde ao arquivo enviado.'
                );
            }
        }
    }
}
