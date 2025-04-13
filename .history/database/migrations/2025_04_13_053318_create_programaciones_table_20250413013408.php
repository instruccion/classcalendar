<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('programaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained()->onDelete('cascade');
            $table->foreignId('grupo_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('instructor_id')->constrained()->onDelete('cascade');
            $table->foreignId('aula_id')->constrained()->onDelete('cascade');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('notificado_inac')->default(false);
            $table->date('fecha_notificacion_inac')->nullable();
            $table->enum('estado', ['programado', 'confirmado'])->default('programado');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('programaciones');
    }
};
