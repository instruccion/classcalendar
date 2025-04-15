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
        Schema::create('colores_disponibles', function (Blueprint $table) {
            $table->id();
            $table->string('color', 20)->unique();
            $table->boolean('disponible')->default(true);
            $table->timestamps();
        });

        // Insertar colores de alta visibilidad automáticamente
        DB::table('colores_disponibles')->insert([
            ['color' => '#FF0000', 'disponible' => true],
            ['color' => '#007BFF', 'disponible' => true],
            ['color' => '#28A745', 'disponible' => true],
            ['color' => '#FFC107', 'disponible' => true],
            ['color' => '#6610F2', 'disponible' => true],
            ['color' => '#E83E8C', 'disponible' => true],
            ['color' => '#FD7E14', 'disponible' => true],
            ['color' => '#20C997', 'disponible' => true],
            ['color' => '#6F42C1', 'disponible' => true],
            ['color' => '#17A2B8', 'disponible' => true],
            ['color' => '#343A40', 'disponible' => true],
            ['color' => '#FF69B4', 'disponible' => true],
            ['color' => '#00CED1', 'disponible' => true],
            ['color' => '#FF6347', 'disponible' => true],
            ['color' => '#ADFF2F', 'disponible' => true],
        ]);
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colores_disponibles');
    }
};
