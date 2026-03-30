<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE emendas DROP CONSTRAINT IF EXISTS emendas_status_check');

        schema::table('emendas', function (Blueprint $table) {
            $table->string('status', 255)->default('pendente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('emendas')
            ->whereNotIn('status', ['pendente', 'aprovado', 'rejeitado', 'revisao'])
            ->update(['status' => 'pendente']);

        DB::statement('
            ALTER TABLE emendas 
            ALTER COLUMN status TYPE varchar(255) 
            USING status::varchar(255)
        ');

        DB::statement("
            ALTER TABLE emendas 
            ADD CONSTRAINT emendas_status_check 
            CHECK (status IN ('pendente', 'aprovado', 'rejeitado', 'revisao'))
        ");
    }
};
