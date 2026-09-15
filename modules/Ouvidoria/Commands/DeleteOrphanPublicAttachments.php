<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sweeps the public upload prefix.
 *
 * A citizen uploads straight to storage (Fase 5) and only then posts the
 * create; on a successful create the media library attaches the object and
 * deletes it from the prefix. So whatever is still here is an upload whose
 * create never landed — a 422 the citizen never fixed, or an abandoned form.
 * Nothing else reaps it: `app:delete-bucket-temp-files` only wipes `tmp`.
 *
 * Age-based on purpose: the prefix is also where in-flight uploads live for
 * the seconds between PUT and create, so a blunt wipe would delete a
 * manifestation's attachment out from under it.
 */
class DeleteOrphanPublicAttachments extends Command
{
    protected $signature = 'ouvidoria:delete-orphan-attachments {--hours=24 : Minimum age, in hours, before an upload counts as abandoned}';

    protected $description = 'Delete public manifestation uploads whose create never completed.';

    private const string PREFIX = 'public-manifestations';

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $cutoff = now()->subHours($hours)->getTimestamp();

        $disk = Storage::disk('central');
        $deleted = 0;

        foreach ($disk->files(self::PREFIX) as $path) {
            if ($disk->lastModified($path) >= $cutoff) {
                continue;
            }

            $disk->delete($path);
            $deleted++;
        }

        $this->info("Anexos órfãos removidos: {$deleted}.");

        return self::SUCCESS;
    }
}
