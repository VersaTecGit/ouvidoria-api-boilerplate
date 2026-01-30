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
        Schema::create('emendas', function (Blueprint $table) {
            $table->id();

            $table->string('numero')->unique();
            $table->string('exercicio');

            $table->unsignedBigInteger('concedente_id');
            $table->foreign('concedente_id')->references('id')->on('concedentes');

            $table->unsignedBigInteger('recebedor_id');
            $table->foreign('recebedor_id')->references('id')->on('recebedores');

            $table->unsignedBigInteger('modalidade_id');
            $table->foreign('modalidade_id')->references('id')->on('modalidades_transferencias');

            $table->boolean('rascunho')->default(true);

            $table->enum('tipo_objeto', [
                'saude',
                'educacao',
                'infraestrutura',
                'assistencia_social',
            ]);

            $table->enum('status', [
                'pendente',
                'aprovada',
                'rejeitada',
                'revisao'
            ])->default('pendente');

            $table->enum('gnd', [
                'gnd3',  // Custeio
                'gnd4',  // Investimento
                'outro',
            ]);

            $table->text('descricao_objeto');
            $table->decimal('valor', 15, 2);
            $table->string('responsavel');

            $table->boolean('anuencia_sus')->nullable()->default(null);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['exercicio', 'modalidade_id']);
            $table->index(['tipo_objeto', 'gnd']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emendas');
    }
};
