<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportedFilesTable extends Migration
{
    public function up(): void
    {
        Schema::create('imported_files', function (Blueprint $table) {
            $table->id();
            $table->string('filename'); // Nome original do arquivo
            $table->string('path'); // Caminho onde o arquivo está armazenado no disco
            $table->enum('status', ['pending', 'processing', 'completed', 'failed']); // Status do arquivo
            $table->enum('source', ['upload', 'email']); // De onde veio o arquivo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imported_files');
    }
}
