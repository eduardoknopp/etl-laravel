<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransformationMapsTable extends Migration
{
    public function up(): void
    {
        Schema::create('transformation_maps', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome do mapa (ex: "XML para XLS")
            $table->enum('from_type', ['xml', 'xls', 'csv', 'json']); // Tipo de arquivo de origem
            $table->enum('to_type', ['xml', 'xls', 'csv', 'json']); // Tipo de arquivo de destino
            $table->json('rules'); // Regras de transformação (um JSON com campos e mapeamento)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transformation_maps');
    }
}
