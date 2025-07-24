@extends('layouts.app')

@section('title', 'Dashboard TransM')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard TransM</h1>
            <p class="text-gray-600">Visão geral das operações de transporte</p>
        </div>
        <div class="flex gap-3">
            <select id="periodFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="7">Últimos 7 dias</option>
                <option value="14">Últimos 14 dias</option>
                <option value="30" selected>Últimos 30 dias</option>
            </select>
            <button class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i data-lucide="calendar" class="h-4 w-4"></i>
                Período: <span id="periodText">30</span> dias
            </button>
            <button class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i data-lucide="database" class="h-4 w-4"></i>
                Exportar Relatório
            </button>
        </div>
    </div>

    <!-- KPIs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pedidos Ativos</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $kpis['pedidos_ativos'] }}</p>
                    <div class="flex items-center text-xs text-gray-500">
                        <span class="font-medium text-green-600">+12%</span>
                        <span class="ml-1">vs mês anterior</span>
                    </div>
                </div>
                <i data-lucide="arrow-up" class="h-4 w-4 text-green-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Valor Total</p>
                    <p class="text-2xl font-bold text-gray-900">R$ {{ number_format($kpis['valor_total'] / 1000, 1) }}K</p>
                    <div class="flex items-center text-xs text-gray-500">
                        <span class="font-medium text-green-600">+8.2%</span>
                        <span class="ml-1">faturamento mensal</span>
                    </div>
                </div>
                <i data-lucide="arrow-up" class="h-4 w-4 text-green-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Entregas Concluídas</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $kpis['taxa_entrega'] }}%</p>
                    <div class="flex items-center text-xs text-gray-500">
                        <span class="font-medium text-red-600">-2.1%</span>
                        <span class="ml-1">taxa de sucesso</span>
                    </div>
                </div>
                <i data-lucide="arrow-down" class="h-4 w-4 text-red-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Cargas em Trânsito</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $kpis['em_transito'] }}</p>
                    <div class="flex items-center text-xs text-gray-500">
                        <span class="font-medium text-green-600">+5</span>
                        <span class="ml-1">cargas ativas</span>
                    </div>
                </div>
                <i data-lucide="arrow-up" class="h-4 w-4 text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Indicadores de Cargas por Período -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex items-center gap-2">
                    <i data-lucide="database" class="h-5 w-5"></i>
                    <h3 class="text-lg font-semibold">Cargas por Período</h3>
                </div>
                <p class="text-sm text-gray-600">Quantidade de cargas registradas por período</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 border rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $cargas_periodo['hoje'] }}</div>
                        <div class="text-sm text-gray-600">Hoje</div>
                    </div>
                    <div class="text-center p-4 border rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $cargas_periodo['7_dias'] }}</div>
                        <div class="text-sm text-gray-600">7 dias</div>
                    </div>
                    <div class="text-center p-4 border rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $cargas_periodo['14_dias'] }}</div>
                        <div class="text-sm text-gray-600">14 dias</div>
                    </div>
                    <div class="text-center p-4 border rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $cargas_periodo['30_dias'] }}</div>
                        <div class="text-sm text-gray-600">30 dias</div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t">
                    <div class="text-sm text-gray-600">
                        Última atualização: {{ now()->format('d/m/Y H:i:s') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Geral -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex items-center gap-2">
                    <i data-lucide="grid-2x2" class="h-5 w-5"></i>
                    <h3 class="text-lg font-semibold">Status Geral</h3>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span>Ocupação da Frota</span>
                        <span class="font-medium">78%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: 78%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span>Eficiência de Entrega</span>
                        <span class="font-medium">94%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: 94%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span>Satisfação do Cliente</span>
                        <span class="font-medium">91%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: 91%"></div>
                    </div>
                </div>

                <div class="pt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Motoristas Ativos</span>
                        <span class="font-medium">{{ $stats['motoristas_ativos'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Veículos Disponíveis</span>
                        <span class="font-medium">{{ $stats['veiculos_disponiveis'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Rotas Cadastradas</span>
                        <span class="font-medium">{{ $stats['rotas_cadastradas'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Decisão Empresarial -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Faturamento e Margem -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex items-center gap-2">
                    <i data-lucide="trending-up" class="h-5 w-5"></i>
                    <h3 class="text-lg font-semibold">Faturamento vs Margem</h3>
                </div>
                <p class="text-sm text-gray-600">Evolução mensal - Análise de rentabilidade</p>
            </div>
            <div class="p-6">
                <canvas id="faturamentoChart" class="w-full h-[300px]"></canvas>
            </div>
        </div>

        <!-- Status dos Pedidos -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex items-center gap-2">
                    <i data-lucide="database" class="h-5 w-5"></i>
                    <h3 class="text-lg font-semibold">Distribuição de Status</h3>
                </div>
                <p class="text-sm text-gray-600">Situação atual dos pedidos (%)</p>
            </div>
            <div class="p-6">
               {{--  <canvas id="statusChart" class="w-full h-[300px]"></canvas> --}}
                <div class="grid grid-cols-2 gap-2 mt-4">
                    @foreach($status_pedidos as $status)
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $status['color'] }}"></div>
                        <span class="text-xs">{{ $status['name'] }}: {{ $status['value'] }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Performance por Rota e Tipos de Caminhão -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Performance das Rotas -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex items-center gap-2">
                    <i data-lucide="dollar-sign" class="h-5 w-5"></i>
                    <h3 class="text-lg font-semibold">Top 5 Rotas - Receita</h3>
                </div>
                <p class="text-sm text-gray-600">Rotas mais lucrativas do mês</p>
            </div>
            <div class="p-6">
                <canvas id="rotasChart" class="w-full h-[300px]"></canvas>
            </div>
        </div>

        <!-- Utilização por Tipo de Caminhão -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex items-center gap-2">
                    <i data-lucide="grid-2x2" class="h-5 w-5"></i>
                    <h3 class="text-lg font-semibold">Utilização da Frota</h3>
                </div>
                <p class="text-sm text-gray-600">Uso dos tipos de caminhão</p>
            </div>
            <div class="p-6">
                <canvas id="caminhaoChart" class="w-full h-[300px]"></canvas>
            </div>
        </div>
    </div>

    <!-- Projeções -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Projeções de Faturamento -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex items-center gap-2">
                    <i data-lucide="trending-up" class="h-5 w-5"></i>
                    <h3 class="text-lg font-semibold">Projeções de Faturamento</h3>
                </div>
                <p class="text-sm text-gray-600">Previsões baseadas em dados históricos</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-600">Próximos 7 dias</p>
                        <p class="text-xl font-bold text-blue-600">R$ {{ number_format($projecoes['faturamento']['7_dias'] / 1000, 1) }}K</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-green-600">+15%</p>
                        <p class="text-xs text-gray-500">vs período anterior</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-600">Próximos 14 dias</p>
                        <p class="text-xl font-bold text-blue-600">R$ {{ number_format($projecoes['faturamento']['14_dias'] / 1000, 1) }}K</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-green-600">+12%</p>
                        <p class="text-xs text-gray-500">vs período anterior</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-600">Próximos 30 dias</p>
                        <p class="text-xl font-bold text-blue-600">R$ {{ number_format($projecoes['faturamento']['30_dias'] / 1000, 1) }}K</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-green-600">+18%</p>
                        <p class="text-xs text-gray-500">vs período anterior</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projeções de Pedidos -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex items-center gap-2">
                    <i data-lucide="database" class="h-5 w-5"></i>
                    <h3 class="text-lg font-semibold">Projeções de Pedidos</h3>
                </div>
                <p class="text-sm text-gray-600">Estimativas de volume de pedidos</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-600">Próximos 7 dias</p>
                        <p class="text-xl font-bold text-blue-600">{{ $projecoes['pedidos']['7_dias'] }} pedidos</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-green-600">+8%</p>
                        <p class="text-xs text-gray-500">vs período anterior</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-600">Próximos 14 dias</p>
                        <p class="text-xl font-bold text-blue-600">{{ $projecoes['pedidos']['14_dias'] }} pedidos</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-green-600">+10%</p>
                        <p class="text-xs text-gray-500">vs período anterior</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-600">Próximos 30 dias</p>
                        <p class="text-xl font-bold text-blue-600">{{ $projecoes['pedidos']['30_dias'] }} pedidos</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-green-600">+16%</p>
                        <p class="text-xs text-gray-500">vs período anterior</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Novos Gráficos de Análise -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Performance dos Motoristas -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex items-center gap-2">
                    <i data-lucide="user" class="h-5 w-5"></i>
                    <h3 class="text-lg font-semibold">Performance dos Motoristas</h3>
                </div>
                <p class="text-sm text-gray-600">Top 5 motoristas por entregas e pontualidade</p>
            </div>
            <div class="p-6">
                <canvas id="motoristasChart" class="w-full h-[300px]"></canvas>
            </div>
        </div>

        <!-- Custos Operacionais -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex items-center gap-2">
                    <i data-lucide="dollar-sign" class="h-5 w-5"></i>
                    <h3 class="text-lg font-semibold">Custos Operacionais</h3>
                </div>
                <p class="text-sm text-gray-600">Breakdown dos custos mensais</p>
            </div>
            <div class="p-6">
                <canvas id="custosChart" class="w-full h-[300px]"></canvas>
            </div>
        </div>
    </div>

    <!-- Capacidade da Frota -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <div class="flex items-center gap-2">
                <i data-lucide="grid-2x2" class="h-5 w-5"></i>
                <h3 class="text-lg font-semibold">Capacidade e Utilização da Frota</h3>
            </div>
            <p class="text-sm text-gray-600">Análise da utilização da frota ao longo do tempo</p>
        </div>
        <div class="p-6">
            <canvas id="capacidadeChart" class="w-full h-[400px]"></canvas>
        </div>
    </div>

    <!-- Análise de Tendências -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <div class="flex items-center gap-2">
                <i data-lucide="trending-up" class="h-5 w-5"></i>
                <h3 class="text-lg font-semibold">Análise de Tendências - Pedidos vs Faturamento</h3>
            </div>
            <p class="text-sm text-gray-600">Correlação entre volume de pedidos e receita mensal</p>
        </div>
        <div class="p-6">
            <canvas id="tendenciasChart" class="w-full h-[400px]"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Period filter functionality
    const periodFilter = document.getElementById('periodFilter');
    const periodText = document.getElementById('periodText');

    periodFilter.addEventListener('change', function() {
        periodText.textContent = this.value;
        // Aqui você pode adicionar lógica para atualizar os dados
    });

    // Chart.js configurations
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        }
    };

    // Faturamento Chart
    const faturamentoCtx = document.getElementById('faturamentoChart').getContext('2d');
    new Chart(faturamentoCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($faturamento_mensal->pluck('mes')) !!},
            datasets: [{
                label: 'Faturamento',
                data: {!! json_encode($faturamento_mensal->pluck('faturamento')) !!},
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.3)',
                fill: true
            }, {
                label: 'Margem',
                data: {!! json_encode($faturamento_mensal->pluck('margem')) !!},
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.3)',
                fill: true
            }]
        },
        options: chartOptions
    });

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: @json($status_pedidos->pluck('name')),
            datasets: [{
                data: @json($status_pedidos->pluck('value')),
                backgroundColor: @json($status_pedidos->pluck('color')),
            }]
        },
        options: {
            ...chartOptions,
            responsive: true,
    maintainAspectRatio: false,
    animation: false,


            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Performance Rotas Chart
    const rotasCtx = document.getElementById('rotasChart').getContext('2d');
    new Chart(rotasCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($performance_rotas->pluck('rota')) !!},
            datasets: [{
                label: 'Receita',
                data: {!! json_encode($performance_rotas->pluck('receita')) !!},
                backgroundColor: '#3b82f6'
            }]
        },
        options: {
            ...chartOptions,
            indexAxis: 'y'
        }
    });

    // Tipos Caminhão Chart
    const caminhaoCtx = document.getElementById('caminhaoChart').getContext('2d');
    new Chart(caminhaoCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($tipos_caminhao->pluck('tipo')) !!},
            datasets: [{
                label: 'Utilização',
                data: {!! json_encode($tipos_caminhao->pluck('utilizacao')) !!},
                backgroundColor: '#10b981'
            }]
        },
        options: chartOptions
    });

    // Performance Motoristas Chart
    const motoristasCtx = document.getElementById('motoristasChart').getContext('2d');
    new Chart(motoristasCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($performance_motoristas->pluck('nome')) !!},
            datasets: [{
                label: 'Entregas',
                data: {!! json_encode($performance_motoristas->pluck('entregas')) !!},
                backgroundColor: '#3b82f6'
            }, {
                label: 'Pontualidade (%)',
                data: {!! json_encode($performance_motoristas->pluck('pontualidade')) !!},
                backgroundColor: '#10b981'
            }]
        },
        options: chartOptions
    });

    // Custos Operacionais Chart
    const custosCtx = document.getElementById('custosChart').getContext('2d');
    new Chart(custosCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($custos_operacionais->pluck('mes')) !!},
            datasets: [{
                label: 'Combustível',
                data: {!! json_encode($custos_operacionais->pluck('combustivel')) !!},
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.3)',
                fill: true
            }, {
                label: 'Manutenção',
                data: {!! json_encode($custos_operacionais->pluck('manutencao')) !!},
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.3)',
                fill: true
            }, {
                label: 'Pedágio',
                data: {!! json_encode($custos_operacionais->pluck('pedagio')) !!},
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.3)',
                fill: true
            }]
        },
        options: chartOptions
    });

    // Capacidade da Frota Chart
    const capacidadeCtx = document.getElementById('capacidadeChart').getContext('2d');
    new Chart(capacidadeCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($capacidade_frota->pluck('mes')) !!},
            datasets: [{
                label: 'Utilização (%)',
                data: {!! json_encode($capacidade_frota->pluck('utilizacao')) !!},
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.3)',
                fill: true
            }, {
                label: 'Disponível (%)',
                data: {!! json_encode($capacidade_frota->pluck('disponivel')) !!},
                borderColor: '#94a3b8',
                backgroundColor: 'rgba(148, 163, 184, 0.3)',
                fill: true
            }]
        },
        options: chartOptions
    });

    // Tendências Chart
    const tendenciasCtx = document.getElementById('tendenciasChart').getContext('2d');
    new Chart(tendenciasCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($faturamento_mensal->pluck('mes')) !!},
            datasets: [{
                label: 'Faturamento',
                data: {!! json_encode($faturamento_mensal->pluck('faturamento')) !!},
                borderColor: '#3b82f6',
                backgroundColor: 'transparent',
                yAxisID: 'y'
            }, {
                label: 'Margem',
                data: {!! json_encode($faturamento_mensal->pluck('margem')) !!},
                borderColor: '#10b981',
                backgroundColor: 'transparent',
                yAxisID: 'y'
            }, {
                label: 'Pedidos',
                data: {!! json_encode($faturamento_mensal->pluck('pedidos')) !!},
                borderColor: '#f59e0b',
                backgroundColor: 'transparent',
                borderDash: [5, 5],
                yAxisID: 'y1'
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left'
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
});
</script>
@endsection
