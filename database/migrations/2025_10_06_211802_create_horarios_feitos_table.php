<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('horarios_feitos', function (Blueprint $table) {
      $table->id();
      $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
      $table->boolean('publicado')->default(false)->index();
      $table->timestamp('publicado_em')->nullable();
      $table->timestamps();

      $table->unique('turma_id'); // 1:1 com Turma
    });
  }
  public function down(): void {
    Schema::dropIfExists('horarios_feitos');
  }
};
