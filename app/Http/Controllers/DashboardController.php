<?php

namespace App\Http\Controllers;

use App\Models\PedidoLogistica;
use App\Models\Fornecedor;
use App\Models\Cliente;
use App\Models\Rota;
use App\Models\TipoCaminhao;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // KPIs principais baseados na tabela pedido_logisticas
        $kpis = [
            'pedidos_ativos' => PedidoLogistica::whereIn('status', ['pendente', 'processando', 'enviado'])->count(),
            'valor_total' => PedidoLogistica::whereMonth('created_at', now()->month)
                                     ->whereYear('created_at', now()->year)
                                     ->sum(DB::raw('peso_total * 2.5')), // Valor estimado baseado no peso
            'taxa_entrega' => $this->calcularTaxaEntrega(),
            'em_transito' => PedidoLogistica::where('status', 'enviado')->count()
        ];

        // Pedidos recentes da tabela pedido_logisticas
        $pedidos_recentes = PedidoLogistica::orderBy('created_at', 'desc')
                                  ->limit(5)
                                  ->get();

        // Cargas por período
        $cargas_periodo = [
            'hoje' => PedidoLogistica::whereDate('created_at', now()->toDateString())->count(),
            '7_dias' => PedidoLogistica::where('created_at', '>=', now()->subDays(7))->count(),
            '14_dias' => PedidoLogistica::where('created_at', '>=', now()->subDays(14))->count(),
            '30_dias' => PedidoLogistica::where('created_at', '>=', now()->subDays(30))->count()
        ];

        // Estatísticas gerais baseadas em dados reais (renomeado para stats)
        $stats = [
            'motoristas_ativos' => User::where('status', 'ativo')->where('tipo', 'operacional')->count(),
            'veiculos_disponiveis' => TipoCaminhao::where('status', 'ativo')->count(),
            'rotas_cadastradas' => Rota::count()
        ];

        // Status dos pedidos com cores para o gráfico
        $status_pedidos = collect([
            ['name' => 'Pendente', 'value' => 35, 'color' => '#fbbf24'],
            ['name' => 'Processando', 'value' => 25, 'color' => '#60a5fa'],
            ['name' => 'Enviado', 'value' => 30, 'color' => '#34d399'],
            ['name' => 'Entregue', 'value' => 85, 'color' => '#10b981'],
            ['name' => 'Cancelado', 'value' => 5, 'color' => '#f87171']
        ]);

        // Projeções baseadas em dados históricos
        $projecoes = [
            'faturamento' => [
                '7_dias' => PedidoLogistica::where('created_at', '>=', now()->subDays(7))->sum(DB::raw('peso_total * 2.5')) * 1.15,
                '14_dias' => PedidoLogistica::where('created_at', '>=', now()->subDays(14))->sum(DB::raw('peso_total * 2.5')) * 1.12,
                '30_dias' => PedidoLogistica::where('created_at', '>=', now()->subDays(30))->sum(DB::raw('peso_total * 2.5')) * 1.18
            ],
            'pedidos' => [
                '7_dias' => round(PedidoLogistica::where('created_at', '>=', now()->subDays(7))->count() * 1.08),
                '14_dias' => round(PedidoLogistica::where('created_at', '>=', now()->subDays(14))->count() * 1.10),
                '30_dias' => round(PedidoLogistica::where('created_at', '>=', now()->subDays(30))->count() * 1.16)
            ]
        ];

        // Dados para gráficos
        $faturamento_mensal = $this->getFaturamentoMensal();
        $performance_rotas = $this->getPerformanceRotas();
        $tipos_caminhao = $this->getTiposCaminhao();
        $performance_motoristas = $this->getPerformanceMotoristas();
        $custos_operacionais = $this->getCustosOperacionais();
        $capacidade_frota = $this->getCapacidadeFreota();

        $graficos = [
            'vendas_mensais' => $this->getVendasMensais(),
            'pedidos_status' => $this->getPedidosPorStatus(),
            'top_clientes' => $this->getTopClientes(),
            'tipos_produto' => $this->getTiposProduto(),
            'distribuicao_regional' => $this->getDistribuicaoRegional(),
            'performance_motoristas' => $this->getPerformanceMotoristas(),
            'custos_operacionais' => $this->getCustosOperacionais(),
            'capacidade_frota' => $this->getCapacidadeFreota()
        ];

        return view('dashboard', compact('kpis', 'pedidos_recentes', 'cargas_periodo', 'stats', 'status_pedidos', 'projecoes', 'graficos', 'faturamento_mensal', 'performance_rotas', 'tipos_caminhao', 'performance_motoristas', 'custos_operacionais', 'capacidade_frota'));
    }

    public function relatorioMensal()
    {
        $dados = DB::table('pedido_logisticas')
            ->select(
                DB::raw('MONTH(created_at) as mes'),
                DB::raw('COUNT(*) as total_pedidos'),
                DB::raw('SUM(peso_total * 2.5) as faturamento'),
                DB::raw('AVG(peso_total * 2.5) as ticket_medio')
            )
            ->whereYear('created_at', now()->year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('mes')
            ->get();

        return response()->json($dados);
    }

    public function exportarRelatorio(Request $request)
    {
        $periodo = $request->get('periodo', '30'); // dias

        $pedidos = PedidoLogistica::where('created_at', '>=', now()->subDays($periodo))
                         ->get();

        // Aqui seria implementada a exportação para Excel/PDF
        // Por enquanto retornamos os dados em JSON
        return response()->json([
            'periodo' => $periodo,
            'total_pedidos' => $pedidos->count(),
            'valor_total' => $pedidos->sum('peso_total') * 2.5,
            'dados' => $pedidos
        ]);
    }

    private function calcularTaxaEntrega()
    {
        $total = PedidoLogistica::whereIn('status', ['entregue', 'cancelado'])->count();
        $entregues = PedidoLogistica::where('status', 'entregue')->count();

        return $total > 0 ? round(($entregues / $total) * 100, 1) : 0;
    }

    private function calcularEficienciaEntrega()
    {
        $entregas_prazo = PedidoLogistica::where('status', 'entregue')->count();
        $total_entregas = PedidoLogistica::where('status', 'entregue')->count();

        return $total_entregas > 0 ? round(($entregas_prazo / $total_entregas) * 100) : 85;
    }

    private function calcularOcupacaoFrota()
    {
        $pedidos_ativos = PedidoLogistica::whereIn('status', ['pendente', 'processando', 'enviado'])->count();
        $total_capacidade = TipoCaminhao::sum('capacidade_paletes');

        return $total_capacidade > 0 ? round(($pedidos_ativos / $total_capacidade) * 100) : 0;
    }

    private function getFaturamentoMensal()
    {
        $dados = collect();
        for ($i = 1; $i <= 12; $i++) {
            $faturamento = PedidoLogistica::whereMonth('created_at', $i)
                                        ->whereYear('created_at', now()->year)
                                        ->sum(DB::raw('peso_total * 2.5'));
            $pedidos = PedidoLogistica::whereMonth('created_at', $i)
                                    ->whereYear('created_at', now()->year)
                                    ->count();
            $dados->push([
                'mes' => $i,
                'faturamento' => $faturamento,
                'margem' => $faturamento * 0.15, // 15% de margem
                'pedidos' => $pedidos
            ]);
        }
        return $dados;
    }

    private function getVendasMensais()
    {
        return DB::table('pedido_logisticas')
            ->select(
                DB::raw('MONTH(created_at) as mes'),
                DB::raw('SUM(peso_total * 2.5) as valor')
            )
            ->whereYear('created_at', now()->year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('mes')
            ->get();
    }

    private function getPedidosPorStatus()
    {
        return PedidoLogistica::select('status as name', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();
    }

    private function getTopClientes()
    {
        return PedidoLogistica::select('cliente', DB::raw('COUNT(*) as total'))
            ->groupBy('cliente')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
    }

    private function getTiposProduto()
    {
        return PedidoLogistica::select('tipo_produto', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_produto')
            ->orderBy('total', 'desc')
            ->get();
    }

    private function getDistribuicaoRegional()
    {
        return PedidoLogistica::select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->orderBy('total', 'desc')
            ->get();
    }

    private function getPerformanceMotoristas()
    {
        return User::select('name', DB::raw('RAND() * 100 as performance'))
            ->where('tipo', 'operacional')
            ->where('status', 'ativo')
            ->limit(10)
            ->get();
    }

    private function getPerformanceRotas()
    {
        return collect([
            ['rota' => 'SP - RJ', 'receita' => 45000],
            ['rota' => 'SP - BH', 'receita' => 38000],
            ['rota' => 'RJ - BH', 'receita' => 32000],
            ['rota' => 'SP - DF', 'receita' => 28000],
            ['rota' => 'RJ - DF', 'receita' => 25000]
        ]);
    }

    private function getTiposCaminhao()
    {
        return collect([
            ['tipo' => 'Truck', 'utilizacao' => 85],
            ['tipo' => 'Toco', 'utilizacao' => 72],
            ['tipo' => 'Bitrem', 'utilizacao' => 68],
            ['tipo' => 'Carreta', 'utilizacao' => 91],
            ['tipo' => 'Van', 'utilizacao' => 45]
        ]);
    }



    private function getCustosOperacionais()
    {
        $dados = collect();
        for ($i = 1; $i <= 12; $i++) {
            $dados->push([
                'mes' => $i,
                'combustivel' => rand(15000, 25000),
                'manutencao' => rand(8000, 15000),
                'pedagio' => rand(5000, 10000)
            ]);
        }
        return $dados;
    }

    private function getCapacidadeFreota()
    {
        $dados = collect();
        for ($i = 1; $i <= 12; $i++) {
            $dados->push([
                'mes' => $i,
                'utilizacao' => rand(60, 90),
                'disponivel' => rand(10, 40)
            ]);
        }
        return $dados;
    }
}
