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
            if (!Schema::hasColumn('programaciones', 'estado_confirmacion')) {
                $table->enum('estado_confirmacion', ['pendiente', 'confirmado', 'rechazado'])->default('pendiente');
            }

            if (!Schema::hasColumn('programaciones', 'fecha_confirmacion')) {
                $table->timestamp('fecha_confirmacion')->nullable();
            }

            if (!Schema::hasColumn('programaciones', 'motivo_rechazo')) {
                $table->text('motivo_rechazo')->nullable();
            }
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programaciones', function (Blueprint $table) {
            //
        });
    }
};
