<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


public function up()
{
    Schema::table('programaciones', function (Blueprint $table) {
        $table->foreignId('instructor_id')->nullable()->change();
        $table->foreignId('aula_id')->nullable()->change();
    });
}
