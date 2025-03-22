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
        Schema::table('imported_files', function (Blueprint $table) {
            $table->string('original_filename')->nullable()->after('filename');
            $table->bigInteger('size')->nullable()->after('path');
            $table->string('mime_type')->nullable()->after('size');
            $table->json('metadata')->nullable()->after('source');
            
            // Atualizando a enumeração de source para incluir 'test' e 'analysis'
            $table->dropColumn('source');
        });
        
        // Re-adicionar o campo source com os novos valores permitidos
        Schema::table('imported_files', function (Blueprint $table) {
            $table->enum('source', ['upload', 'email', 'test', 'analysis'])->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imported_files', function (Blueprint $table) {
            $table->dropColumn(['original_filename', 'size', 'mime_type', 'metadata']);
            $table->dropColumn('source');
        });
        
        // Re-adicionar o campo source com os valores originais
        Schema::table('imported_files', function (Blueprint $table) {
            $table->enum('source', ['upload', 'email'])->after('status');
        });
    }
};
