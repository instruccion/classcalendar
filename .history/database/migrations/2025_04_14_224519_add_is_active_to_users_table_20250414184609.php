<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Agregar el campo 'is_active' con valor por defecto 1 (activo)
            $table->boolean('is_active')->default(1);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar el campo 'is_active' si la migración es revertida
            $table->dropColumn('is_active');
        });
    }

};
