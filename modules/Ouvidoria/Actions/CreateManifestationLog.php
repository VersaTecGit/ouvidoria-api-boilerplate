<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Ouvidoria\DTOs\CreateManifestationLogDTO;
use Modules\Ouvidoria\Models\Manifestation;
use Modules\Ouvidoria\Models\ManifestationLog;

/**
 * Writes a timeline entry. This is the only channel back to the citizen:
 * there is no e-mail and no messaging, so a public log is what "answering"
 * actually means here.
 *
 * When the entry carries a status, the manifestation moves to it in the same
 * transaction — the timeline and the status never disagree.
 */
final readonly class CreateManifestationLog
{
    public function __construct(
        private FetchManifestation $fetchManifestation,
    ) {}

    public function handle(string $uuid, CreateManifestationLogDTO $dto): ManifestationLog
    {
        $manifestation = $this->fetchManifestation->handle($uuid);

        DB::beginTransaction();
        try {
            $log = $this->write(
                $manifestation,
                $dto->content,
                $dto->is_public,
                $dto->status?->value,
            );

            if ($dto->status !== null) {
                $manifestation->status = $dto->status;
                $manifestation->save();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $log;
    }

    /**
     * Shared by `RespondManifestation`, which must write its public entry
     * inside its own transaction.
     */
    public function write(
        Manifestation $manifestation,
        string $content,
        bool $isPublic,
        ?string $status = null,
    ): ManifestationLog {
        $log = new ManifestationLog([
            'manifestation_id' => $manifestation->id,
            'content' => $content,
            'is_public' => $isPublic,
            'status' => $status ?? $manifestation->status->value,
            'author_id' => auth()->id(),
        ]);

        $log->save();

        return $log;
    }
}
