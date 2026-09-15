<?php

declare(strict_types=1);

namespace Tests\Feature\Ouvidoria;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * A successful create hands the upload to the media library, which deletes the
 * temporary object — so anything left under `public-manifestations/` belongs to
 * a citizen who uploaded a file and never finished. Those pile up forever
 * unless something sweeps them; this is that sweep.
 */
class PublicAttachmentsCleanupTest extends TestCase
{
    private const string PREFIX = 'public-manifestations';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('central');
    }

    public function test_orphan_older_than_the_window_is_deleted(): void
    {
        $key = $this->putAttachment(ageInHours: 48);

        $this->artisan('ouvidoria:delete-orphan-attachments')->assertSuccessful();

        Storage::disk('central')->assertMissing($key);
    }

    public function test_recent_upload_is_kept(): void
    {
        // A citizen mid-submit: the file is already in the bucket, the create is in flight.
        $key = $this->putAttachment(ageInHours: 1);

        $this->artisan('ouvidoria:delete-orphan-attachments')->assertSuccessful();

        Storage::disk('central')->assertExists($key);
    }

    public function test_objects_outside_the_public_prefix_are_untouched(): void
    {
        Storage::disk('central')->put('media/1/contrato.pdf', 'keep me');
        $this->backdate('media/1/contrato.pdf', hours: 500);

        $this->artisan('ouvidoria:delete-orphan-attachments')->assertSuccessful();

        Storage::disk('central')->assertExists('media/1/contrato.pdf');
    }

    public function test_window_is_configurable(): void
    {
        $key = $this->putAttachment(ageInHours: 5);

        $this->artisan('ouvidoria:delete-orphan-attachments', ['--hours' => 2])
            ->assertSuccessful();

        Storage::disk('central')->assertMissing($key);
    }

    private function putAttachment(int $ageInHours): string
    {
        $key = self::PREFIX . '/' . Str::uuid() . '.pdf';

        Storage::disk('central')->put($key, '%PDF-1.4 fake');
        $this->backdate($key, $ageInHours);

        return $key;
    }

    private function backdate(string $key, int $hours): void
    {
        touch(Storage::disk('central')->path($key), now()->subHours($hours)->getTimestamp());
    }
}
