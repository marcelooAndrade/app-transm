<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RotaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ScrapingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TipoCaminhaoController;
use App\Http\Controllers\ImportacaoPedidoController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('pedidos', PedidoController::class);
Route::post('/pedidos/{id}/marcar-entregue', [PedidoController::class, 'marcarComoEntregue'])->name('pedidos.marcar-entregue');
Route::get('/pedidos/{id}/ordem-servico', [PedidoController::class, 'gerarOrdemServico'])->name('pedidos.ordem-servico');
Route::post('/pedidos/importar', [ImportacaoPedidoController::class, 'upload'])->name('pedidos.upload');
Route::post('/pedidos/scraping', [ImportacaoPedidoController::class, 'scraping'])->name('pedidos.scraping');


Route::resource('usuarios', UserController::class);
Route::resource('rotas', RotaController::class);
Route::resource('tipos-caminhao', TipoCaminhaoController::class);

Route::get('/relatorio-mensal', [DashboardController::class, 'relatorioMensal'])->name('relatorio.mensal');
Route::get('/exportar-relatorio', [DashboardController::class, 'exportarRelatorio'])->name('relatorio.exportar');
Route::post('/scraping/importar', [ScrapingController::class, 'executar'])->name('scraping.executar');
Route::get('/scraping/stream', [ScrapingController::class, 'stream'])->name('scraping.stream');
