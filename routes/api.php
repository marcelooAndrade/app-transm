<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportacaoPedidoController;

Route::post('/pedidos/scraping', [ImportacaoPedidoController::class, 'scraping']);


