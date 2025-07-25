@extends('layouts.app')

@section('title', 'Montagem de Cargas')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-foreground">Montagem de Cargas</h1>
            <p class="text-muted-foreground">Acompanhe e gerencie cargas agrupadas por rota</p>
        </div>
        <a href="{{ route('cargas.create') }}"
            class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Nova Carga
        </a>
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('cargas.index') }}"
        class="bg-card p-6 rounded-lg border border-border shadow-sm">
        <h3 class="text-lg font-semibold mb-4">🔍 Filtros</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="space-y-2 md:col-span-2">
                <label class="text-sm font-medium">Buscar</label>
                <div class="relative">
                    <i data-lucide="search"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nome da carga, rota, cidade..."
                        class="w-full pl-10 px-3 py-2 border border-border rounded-lg bg-background">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium">Rota</label>
                <select name="rota_id" class="w-full px-3 py-2 border border-border rounded-lg bg-background">
                    <option value="">Todas</option>
                    @foreach($rotas as $rota)
                        <option value="{{ $rota->id }}" {{ request('rota_id') == $rota->id ? 'selected' : '' }}>
                            {{ $rota->nome }} ({{ $rota->cidade }} - {{ $rota->estado }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2 mt-4 md:mt-0">
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Aplicar
                </button>
                <a href="{{ route('cargas.index') }}"
                    class="px-4 py-2 text-sm text-red-600 hover:underline font-medium">Limpar</a>
            </div>
        </div>
    </form>

    <!-- Lista de Cargas -->
    <div class="bg-card rounded-lg border border-border">
        <div class="p-6 border-b border-border">
            <h3 class="text-lg font-semibold">🚚 Cargas Montadas</h3>
            <p class="text-sm text-muted-foreground">{{ $cargas->total() }} registros encontrados</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-border">
                    <tr>
                        <th class="text-left p-4">ID</th>
                        <th class="text-left p-4">Nome</th>
                        <th class="text-left p-4">Rota</th>
                        <th class="text-left p-4">Pedidos</th>
                        <th class="text-left p-4">Criado em</th>
                        <th class="text-left p-4">Status</th>
                        <th class="text-left p-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cargas as $carga)
                    <tr class="border-b border-border hover:bg-secondary/30">
                        <td class="p-4 font-medium">{{ $carga->id }}</td>
                        <td class="p-4">{{ $carga->nome }}</td>
                        <td class="p-4">{{ $carga->rota->nome ?? '-' }}</td>
                        <td class="p-4">{{ $carga->pedidos->count() }} pedidos</td>
                        <td class="p-4 text-sm">{{ $carga->created_at->format('d/m/Y') }}</td>
                        <td class="p-4 text-sm">{{ $carga->status }}</td>
                        <td class="p-4">
                            <a href="{{ route('cargas.edit', $carga->id) }}"
                                class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-yellow-500 rounded-md hover:bg-yellow-600 transition">
                                <i data-lucide="edit" class="w-4 h-4"></i> Editar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-muted-foreground">Nenhuma carga encontrada.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">
                {{ $cargas->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
