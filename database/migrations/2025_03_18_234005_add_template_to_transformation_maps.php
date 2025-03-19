<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transformation_maps', function (Blueprint $table) {
            $table->string('template')->nullable()->after('rules');
        });
    }

    public function down(): void
    {
        Schema::table('transformation_maps', function (Blueprint $table) {
            $table->dropColumn('template');
        });
    }
};
