<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('instructores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coordinacion_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->string('especialidad')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('instructores');
    }
};
