<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedido_logisticas', function (Blueprint $table) {
           $table->id();
            $table->string('representante')->nullable();
            $table->date('data_pedido')->nullable();
            $table->string('cliente')->nullable();
            $table->string('numero_pedido')->nullable();
            $table->string('codigo_produto')->nullable();
            $table->string('descricao_produto')->nullable();
            $table->string('industria')->nullable();
            $table->integer('qtd_pallets')->nullable();
            $table->string('tipo_produto')->nullable();
            $table->decimal('total_m2', 10, 2)->nullable();
            $table->decimal('peso_total', 10, 2)->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
};
