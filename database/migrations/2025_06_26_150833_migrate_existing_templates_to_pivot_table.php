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
        // Migrar dados existentes de template_documentos para a tabela pivot
        $templates = DB::table('template_documentos')
                      ->whereNotNull('grupo_exigencia_id')
                      ->get();

        foreach ($templates as $template) {
            // Inserir na tabela pivot
            DB::table('grupo_exigencia_template_documento')->insert([
                'grupo_exigencia_id' => $template->grupo_exigencia_id,
                'template_documento_id' => $template->id,
                'is_obrigatorio' => $template->is_obrigatorio ?? true,
                'ordem' => $template->ordem ?? 0,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "Migrados " . $templates->count() . " templates para a tabela pivot.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Limpar a tabela pivot
        DB::table('grupo_exigencia_template_documento')->truncate();
        
        echo "Tabela pivot limpa.\n";
    }
};
