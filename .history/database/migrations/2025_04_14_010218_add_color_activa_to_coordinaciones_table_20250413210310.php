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
        Schema::table('coordinaciones', function (Blueprint $table) {
            $table->string('color', 20)->nullable()->after('descripcion');
            $table->boolean('activa')->default(true)->after('color');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coordinaciones', function (Blueprint $table) {
            $table->dropColumn(['color', 'activa']);
        });
    }
};
