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
        Schema::table('instructores', function (Blueprint $table) {
            // Añade la columna 'activo', tipo booleano, por defecto true (activo)
            // Colócala después de una columna existente, ej, 'telefono'
            $table->boolean('activo')->default(true)->after('telefono');
        });
    }

    public function down(): void
    {
        Schema::table('instructores', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
