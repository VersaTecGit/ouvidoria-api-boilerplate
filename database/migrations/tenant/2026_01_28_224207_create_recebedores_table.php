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
        Schema::create('recebedores', function (Blueprint $table) {
            $table->id();

            $table->string('razao_social');
            $table->string('cnpj', 14)->unique();

            $table->enum('tipo', ['prefeitura', 'estado', 'entidade', 'outro'])->default('prefeitura');

            $table->string('municipio');

            $table->string('uf', 2);
            $table->unsignedInteger('codigo_ibge');

            $table->timestamps();
            $table->softDeletes();

            // Índices úteis
            $table->index('codigo_ibge');
            $table->index('uf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recebedores');
    }
};
