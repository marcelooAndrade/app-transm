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
        Schema::create('carga_pedido', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('carga_id');
            $table->unsignedBigInteger('pedido_id');
            $table->timestamps();

            $table->foreign('carga_id')->references('id')->on('cargas')->onDelete('cascade');
            $table->foreign('pedido_id')->references('id')->on('pedido_logisticas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carga_pedido');
    }
};
