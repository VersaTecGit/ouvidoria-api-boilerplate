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
        Schema::create('eventos_financeiros', function (Blueprint $table) {
            $table->id();

            $table->string('agencia', 10);

            $table->string('conta_corrente', 20);

            $table->enum('tipo', [
                'liquidacao',
                'disponibilizacao',
                'empenho',
                'pagamento',
            ]);

            $table->decimal('valor', 15, 2);

            $table->string('observacao')->nullable();

            $table->foreignId('emenda_id')
                  ->constrained('emendas')
                  ->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Índices úteis
            $table->index(['emenda_id', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos_financeiros');
    }
};
