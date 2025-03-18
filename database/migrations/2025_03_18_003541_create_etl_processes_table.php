<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEtlProcessesTable extends Migration
{
    public function up()
    {
        Schema::create('etl_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('imported_files'); // Relacionamento com o arquivo
            $table->foreignId('map_id')->constrained('transformation_maps'); // Relacionamento com o mapa de transformação
            $table->enum('status', ['processing', 'completed', 'failed']); // Status do processo
            $table->timestamp('processed_at')->nullable(); // Data de término
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('etl_processes');
    }
}
