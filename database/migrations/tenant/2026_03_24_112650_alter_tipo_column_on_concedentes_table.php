<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE concedentes DROP CONSTRAINT IF EXISTS concedentes_tipo_check');

        Schema::table('concedentes', function (Blueprint $table) {
            $table->string('tipo')->change();
        });
    }

    public function down(): void
    {
        DB::table('concedentes')
            ->whereNotIn('tipo', ['parlamentar', 'bancada', 'comissao', 'outro'])
            ->update(['tipo' => 'outro']);

        DB::statement('
            ALTER TABLE concedentes 
            ALTER COLUMN tipo TYPE varchar(255) 
            USING tipo::varchar(255)
        ');

        DB::statement("
            ALTER TABLE concedentes 
            ADD CONSTRAINT concedentes_tipo_check 
            CHECK (tipo IN ('parlamentar', 'bancada', 'comissao', 'outro'))
        ");
    }
};
