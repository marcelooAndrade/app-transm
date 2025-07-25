@extends('layouts.app')

@section('title', 'Pedidos')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-foreground">Gestão de Pedidos</h1>
            <p class="text-muted-foreground">Controle completo dos pedidos de frete</p>
        </div>
        <a href="{{ route('pedidos.create') }}"
            class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Novo Pedido
        </a>
    </div>

    <!-- Grid principal: Importações em 2 colunas e Filtros abaixo -->
    <div class="space-y-6">

        <!-- Grid: Importações em 2 colunas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
            <!-- Importar PDF -->
            <div
                class="bg-card p-6 rounded-lg border border-border shadow-sm min-h-[180px] flex flex-col justify-between">
                <h2 class="text-xl font-bold mb-4">📥 Importar PDF</h2>
                <form method="POST" action="{{ route('pedidos.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                        <input type="file" name="arquivo" accept="application/pdf"
                            class="flex-1 px-3 py-2 border border-border rounded-lg bg-background text-sm">
                        <button type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 transition whitespace-nowrap">
                            Importar PDF
                        </button>
                    </div>
                </form>
            </div>

            <!-- Importar via Scraping -->
            <div
                class="bg-card p-6 rounded-lg border border-border shadow-sm min-h-[180px] flex flex-col justify-between">
                <h2 class="text-xl font-bold mb-4">🤖 Importar via Scraping</h2>
                <button onclick="executarScraping()"
                    class="w-full md:w-auto px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition">
                    Executar Scraping
                </button>
            </div>
        </div>


        <!-- Modal de Feedback (fora do grid para sobrepor corretamente) -->
        <div id="modalScraping" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
            <div class="bg-white p-6 rounded-lg max-w-lg w-full shadow-xl">
                <h2 class="text-xl font-semibold mb-4">Importando Dados...</h2>
                <pre id="logSaida"
                    class="text-sm bg-gray-100 p-3 rounded h-64 overflow-y-auto whitespace-pre-line">Aguarde...</pre>
                <button onclick="fecharModal()"
                    class="mt-4 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Fechar</button>
            </div>
        </div>

        <!-- Filtros em linha abaixo -->
        <form method="GET" action="{{ route('pedidos.index') }}"
            class="bg-card p-6 rounded-lg border border-border shadow-sm">
            <h3 class="text-lg font-semibold mb-4">🔎 Filtros</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <!-- Campo de busca -->
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium">Buscar</label>
                    <div class="relative">
                        <i data-lucide="search"
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="ID, cliente, cidade, representante..."
                            class="w-full pl-10 px-3 py-2 border border-border rounded-lg bg-background">
                    </div>
                </div>

                <!-- Filtro de status -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-border rounded-lg bg-background">
                        <option value="all" {{ request('status')==='all' ? 'selected' : '' }}>Todos</option>
                        <option value="Pendente" {{ request('status')==='Pendente' ? 'selected' : '' }}>Pendente
                        </option>
                        <option value="Em Trânsito" {{ request('status')==='Em Trânsito' ? 'selected' : '' }}>Em
                            Trânsito</option>
                        <option value="Entregue" {{ request('status')==='Entregue' ? 'selected' : '' }}>Entregue
                        </option>
                        <option value="Cancelado" {{ request('status')==='Cancelado' ? 'selected' : '' }}>Cancelado
                        </option>
                    </select>
                </div>

                <!-- Botões -->
                <div class="flex gap-2 mt-4 md:mt-0">
                    <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        Aplicar
                    </button>
                    <a href="{{ route('pedidos.index') }}"
                        class="px-4 py-2 text-sm text-red-600 hover:underline font-medium">Limpar</a>
                </div>
            </div>
        </form>
    </div>



    <!-- Tabela de Pedidos -->
    <div class="bg-card rounded-lg border border-border">
        <div class="p-6 border-b border-border">
            <h3 class="text-lg font-semibold">✅ Lista de Pedidos</h3>
            <p class="text-sm text-muted-foreground">{{ $pedidos->total() }} pedidos encontrados</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-border">
                    <tr>
                        <th class="text-left p-4">ID</th>
                        <th class="text-left p-4">Cliente</th>
                        <th class="text-left p-4">Quantidade</th>
                        <th class="text-left p-4">Rota</th>
                        <th class="text-left p-4">Industria</th>
                        <th class="text-left p-4">Status</th>
                        <th class="text-left p-4">Data Entrega</th>
                        <th class="text-left p-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedidos as $pedido)
                    <tr class="border-b border-border hover:bg-secondary/30">
                        <td class="p-4 font-medium">{{ $pedido->numero_pedido }}</td>
                        <td class="p-4">{{ $pedido->cliente }}</td>
                        <td class="p-4">
                            <div class="space-y-1">
                                <div class="text-sm">{{ $pedido->peso_total }} kg</div>
                                <div class="text-xs text-muted-foreground">{{ $pedido->qtd_pallets }} paletes</div>
                                <div class="text-xs text-muted-foreground">{{ $pedido->total_m2 }} m2</div>
                            </div>
                        </td>
                        <td class="p-4 text-sm">{{ $pedido->cidade }} - {{ $pedido->estado }}</td>
                        <td class="p-4">
                            <div class="space-y-1">
                                <div class="font-medium">{{ $pedido->industria }}</div>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">{{ $pedido->status
                                }}</span>
                        </td>
                        <td class="p-4 text-sm">{{ \Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y') }}</td>
                        <!-- COLUNA DE AÇÕES -->
                        <td class="p-4">
                            <a href="{{ route('pedidos.edit', $pedido->id) }}"
                                class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-yellow-500 rounded-md hover:bg-yellow-600 transition">
                                <i data-lucide="edit" class="w-4 h-4"></i> Editar
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">
                {{ $pedidos->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    function executarScraping() {
    document.getElementById('modalScraping').classList.remove('hidden');
    const logEl = document.getElementById('logSaida');
    logEl.textContent = 'Iniciando importação ...\n';

    const evtSource = new EventSource("{{ route('scraping.stream') }}");

    evtSource.onmessage = function(event) {
        logEl.textContent += event.data + "\n";
        logEl.scrollTop = logEl.scrollHeight;

        if (event.data.includes('[FINALIZADO]')) {
            evtSource.close();
        }
    };

    evtSource.onerror = function(err) {
        logEl.textContent += "\nErro ao conectar com o servidor.";
        evtSource.close();
    };
}

function fecharModal() {
    document.getElementById('modalScraping').classList.add('hidden');
    location.reload(); // 🔄 Atualiza a página ao finalizar

}
</script>


@endsection
