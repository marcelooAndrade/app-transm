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
        Schema::create('cargas', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable(); // nome dado pelo usuário para editar depois
            $table->unsignedBigInteger('rota_id')->nullable();
            $table->enum('status', ['aberta', 'fechada'])->default('aberta');
            $table->timestamps();

            $table->foreign('rota_id')->references('id')->on('rotas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargas');
    }
};
