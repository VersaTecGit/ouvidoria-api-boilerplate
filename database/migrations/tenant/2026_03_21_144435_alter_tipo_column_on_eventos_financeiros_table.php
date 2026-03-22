<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('eventos_financeiros', function (Blueprint $table) {
            $table->string('tipo', 50)->change();
            $table->date('data')->after('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos_financeiros', function (Blueprint $table) {
            $table->enum('tipo', [
                'liquidacao',
                'disponibilizacao',
                'empenho',
                'pagamento',
            ])->change();
            $table->dropColumn('data');
        });
    }
};
