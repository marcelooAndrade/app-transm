<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tipos_caminhao', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->decimal('capacidade_toneladas', 8, 2);
            $table->integer('capacidade_paletes');
            $table->decimal('valor_km', 8, 2);
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->integer('utilizacoes')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tipos_caminhao');
    }
};