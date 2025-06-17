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
        Schema::table('acoes', function (Blueprint $table) {
            // Adicionar campo nome após o campo id
            $table->string('nome')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acoes', function (Blueprint $table) {
            $table->dropColumn('nome');
        });
    }
}; 