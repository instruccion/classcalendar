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
        Schema::table('programaciones', function (Blueprint $table) {
            // Añadir columna después de curso_id, por ejemplo. Puede ser nullable.
            $table->string('bloque_codigo', 191)->nullable()->after('curso_id');
        });
    }

    public function down(): void
    {
        Schema::table('programaciones', function (Blueprint $table) {
            $table->dropColumn('bloque_codigo');
        });
    }
};
