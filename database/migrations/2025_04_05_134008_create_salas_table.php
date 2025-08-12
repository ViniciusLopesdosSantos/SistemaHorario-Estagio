<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('salas', function (Blueprint $table) {
            $table->id('id_sala');
            $table->string('nome')->unique();
            $table->integer('capacidade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('salas');
    }
};
