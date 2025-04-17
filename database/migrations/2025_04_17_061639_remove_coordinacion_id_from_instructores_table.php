<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('instructores', function (Blueprint $table) {
            $table->dropColumn('coordinacion_id');
        });
    }

    public function down(): void
    {
        Schema::table('instructores', function (Blueprint $table) {
            $table->unsignedBigInteger('coordinacion_id')->nullable();
        });
    }
};
