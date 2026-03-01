<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Transport\Support\VehicleMaintenanceStatus;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('driver')->nullable()->after('contacts');
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->json('required_license_categories');
            $table->string('plate')->unique();
            $table->string('model');
            $table->string('brand');
            $table->integer('capacity');
            $table->string('color')->nullable();
            $table->json('fuels')->nullable();
            $table->string('manufacture_year');
            $table->string('renavam')->unique();
            $table->string('chassis_number')->unique();
            $table->string('type');
            $table->string('other_type')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
            $table->userActions();

            $table->index(['status', 'type']);
        });

        Schema::create('vehicle_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->json('attributes');
            $table->timestamps();
            $table->softDeletes();
            $table->userActions();
        });

        Schema::create('vehicle_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->json('origin');
            $table->json('destination');
            $table->json('waypoints')->nullable();
            $table->timestamp('departure_at');
            $table->timestamp('return_at')->nullable();
            $table->string('priority');
            $table->text('justification');
            $table->string('status');
            $table->string('protocol_number')->unique();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('response')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->userActions();
        });

        Schema::create('vehicle_request_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_request_id')->constrained()->cascadeOnDelete();
            $table->morphs('vehicle_request_passenger');
            $table->timestamps();
            $table->softDeletes();
            $table->userActions();
        });

        Schema::create('vehicle_refuels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->date('refueled_at');
            $table->integer('odometer');
            $table->decimal('liters', 8, 2);
            $table->decimal('price_per_liter', 10, 2);
            $table->decimal('total_value', 10, 2);
            $table->string('fuel_type');
            $table->json('station_location')->nullable();
            $table->decimal('consumption', 8, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->userActions();

            $table->index(['vehicle_id', 'refueled_at']);
        });

        Schema::create('vehicle_maintenances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('service');
            $table->string('other_service')->nullable();
            $table->timestamp('performed_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('workshop')->nullable();
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('status')->default(VehicleMaintenanceStatus::SCHEDULED->value);
            $table->timestamps();
            $table->softDeletes();
            $table->userActions();

            $table->index(['vehicle_id', 'status', 'type']);
        });

        Schema::create('vehicle_request_travel_allowances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('vehicle_request_id')->constrained()->cascadeOnDelete();
            $table->morphs('beneficiary');
            $table->string('purpose')->nullable();
            $table->decimal('days_requested', 5, 2)->nullable();
            $table->decimal('unit_value', 10, 2);

            $table->timestamps();
            $table->softDeletes();
            $table->userActions();

            $table->index(['vehicle_request_id']);
        });

        Schema::create('vehicle_trips', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('vehicle_request_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('occurrences')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->userActions();
        });

        Schema::create('vehicle_trip_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_trip_id')->constrained()->cascadeOnDelete();
            $table->morphs('vehicle_trip_passenger');
            $table->boolean('was_present')->nullable();
            $table->string('absence_reason')->nullable();
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
        Schema::dropIfExists('vehicle_trip_passengers');
        Schema::dropIfExists('vehicle_trips');
        Schema::dropIfExists('vehicle_request_travel_allowances');
        Schema::dropIfExists('vehicle_maintenances');
        Schema::dropIfExists('vehicle_refuels');
        Schema::dropIfExists('vehicle_request_passengers');
        Schema::dropIfExists('vehicle_requests');
        Schema::dropIfExists('vehicle_documents');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('drivers');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['driver']);
        });
    }
};
