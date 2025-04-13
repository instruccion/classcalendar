<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coordinacion_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->enum('tipo', ['inicial', 'recurrente', 'puntual']);
            $table->integer('duracion_horas');
            $table->boolean('requiere_notificacion_inac')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cursos');
    }
};
