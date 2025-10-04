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
        Schema::create("unidades_curriculares", function (Blueprint $table) {
            $table->id();
            $table->string("uc");
            $table->string("grupo")->nullable();
            $table->string("codigo_uc")->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("unidades_curriculares");
    }
};
