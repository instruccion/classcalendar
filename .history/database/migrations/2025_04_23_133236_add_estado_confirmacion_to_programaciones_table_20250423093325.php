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
            $table->enum('estado_confirmacion', ['pendiente', 'confirmado', 'rechazado'])->default('pendiente');
            $table->timestamp('fecha_confirmacion')->nullable();
            $table->text('motivo_rechazo')->nullable();
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
