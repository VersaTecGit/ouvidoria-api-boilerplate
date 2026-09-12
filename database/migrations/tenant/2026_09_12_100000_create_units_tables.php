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
        Schema::create('unit_types', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->userActions();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->string('code')->index();
            $table->foreignId('unit_type_id')->constrained()->index();
            $table->string('cnpj')->nullable()->index();
            $table->json('address')->nullable();
            $table->json('contacts')->nullable();
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->userActions();
        });

        Schema::create('unit_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('unit_user');
        Schema::dropIfExists('units');
        Schema::dropIfExists('unit_types');
    }
};
