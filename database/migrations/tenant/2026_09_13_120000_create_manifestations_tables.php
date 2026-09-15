<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('manifestations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('protocol_number')->unique();

            $table->string('type')->index();
            $table->string('status')->index();

            $table->foreignId('destination_agency_id')->constrained();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();

            // Postgres does not index foreign keys on its own, and the staff
            // datatable filters by destination agency on every page load.
            $table->index('destination_agency_id', 'manifestations_agency_idx');

            $table->string('subject');
            $table->text('description');
            $table->string('occurrence_place');

            $table->boolean('is_anonymous')->default(false)->index();

            /*
             * Manifestant data is stored flat, as declared at submission time:
             * it is the record of what was said then, not a pointer to a profile
             * that may change later. All columns are null when `is_anonymous`.
             *
             * `user_id` is reserved for the future citizen portal: when an
             * identified manifestant becomes a User, the link is filled in here
             * with no data migration and no change to the public contract.
             */
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('manifestant_name')->nullable();
            $table->string('manifestant_email')->nullable()->index();
            $table->string('manifestant_phone')->nullable();
            $table->string('manifestant_document')->nullable()->index();
            $table->json('manifestant_address')->nullable();

            // Attendant's final opinion. The public timeline log is written alongside it.
            $table->text('parecer')->nullable();
            $table->foreignId('responded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->userActions();
        });

        Schema::create('manifestation_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('manifestation_id')->constrained()->cascadeOnDelete();

            // The timeline is fetched for every manifestation detail view.
            $table->index('manifestation_id', 'manifestation_logs_manifestation_idx');

            $table->text('content');

            /*
             * The only gate between the citizen timeline and internal notes.
             * `PublicManifestationResource` (Fase 4) must filter on it.
             */
            $table->boolean('is_public')->default(false)->index();

            // Snapshot of the status at the time of the log entry, for the timeline.
            $table->string('status')->nullable()->index();

            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
            $table->userActions();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifestation_logs');
        Schema::dropIfExists('manifestations');
    }
};
