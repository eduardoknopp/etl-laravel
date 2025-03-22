<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verifica se a tabela e_t_l_processes existe
        $tableExists = DB::select("SELECT to_regclass('public.e_t_l_processes') as exists")[0]->exists;
        
        if ($tableExists != null) {
            // A tabela existe, então renomeie-a
            DB::statement('ALTER TABLE e_t_l_processes RENAME TO etl_processes');
        }
        
        // Se a tabela etl_processes não existir, recrie-a
        if (!Schema::hasTable('etl_processes')) {
            Schema::create('etl_processes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('file_id')->constrained('imported_files');
                $table->foreignId('map_id')->constrained('transformation_maps');
                $table->enum('status', ['processing', 'completed', 'failed']);
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não fazer nada no down, pois reverter seria problemático
    }
};
