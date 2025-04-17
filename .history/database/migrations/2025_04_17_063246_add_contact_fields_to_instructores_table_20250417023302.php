<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('instructores', function (Blueprint $table) {
            $table->string('correo')->nullable()->after('especialidad');
            $table->string('telefono', 30)->nullable()->after('correo');
        });
    }

    public function down(): void
    {
        Schema::table('instructores', function (Blueprint $table) {
            $table->dropColumn(['correo', 'telefono']);
        });
    }
};
