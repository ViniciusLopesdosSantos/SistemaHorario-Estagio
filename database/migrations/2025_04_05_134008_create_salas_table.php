<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('salas')) {
            Schema::create('salas', function (Blueprint $table) {
                $table->id('id_sala');
                $table->string('nome');
                $table->integer('capacidade');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('salas');
    }
};