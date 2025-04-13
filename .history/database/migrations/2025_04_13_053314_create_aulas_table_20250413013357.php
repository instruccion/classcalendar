<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('lugar');
            $table->integer('capacidad');
            $table->boolean('pizarra')->default(false);
            $table->boolean('computadora')->default(false);
            $table->boolean('videobeam')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('aulas');
    }
};
