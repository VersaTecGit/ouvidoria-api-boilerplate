<?php

declare(strict_types=1);

namespace Modules\Common\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteBucketTempFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-bucket-temp-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all temporary files in the bucket.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Storage::disk('central')->deleteDirectory('tmp');

        return 0;
    }
}
