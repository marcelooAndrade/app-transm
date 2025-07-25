@extends('layouts.app')

@section('title', 'Rotas')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-foreground">Cadastro de Rotas</h1>
            <p class="text-muted-foreground">Gerencie as rotas disponíveis</p>
        </div>
        <a href="{{ route('rotas.create') }}"
            class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Nova Rota
        </a>
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('rotas.index') }}"
        class="bg-card p-6 rounded-lg border border-border shadow-sm">
        <h3 class="text-lg font-semibold mb-4">🔎 Filtros</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="space-y-2 md:col-span-2">
                <label class="text-sm font-medium">Buscar por nome</label>
                <div class="relative">
                    <i data-lucide="search"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nome da rota..."
                        class="w-full pl-10 px-3 py-2 border border-border rounded-lg bg-background">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-border rounded-lg bg-background">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Todos</option>
                    <option value="ativa" {{ request('status') === 'ativa' ? 'selected' : '' }}>Ativa</option>
                    <option value="inativa" {{ request('status') === 'inativa' ? 'selected' : '' }}>Inativa</option>
                </select>
            </div>

            <div class="flex gap-2 mt-4 md:mt-0">
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Aplicar
                </button>
                <a href="{{ route('rotas.index') }}"
                    class="px-4 py-2 text-sm text-red-600 hover:underline font-medium">Limpar</a>
            </div>
        </div>
    </form>

    <!-- Lista de Rotas -->
    <div class="bg-card rounded-lg border border-border">
        <div class="p-6 border-b border-border">
            <h3 class="text-lg font-semibold">📍 Lista de Rotas</h3>
            <p class="text-sm text-muted-foreground">{{ $rotas->total() }} registros encontrados</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-border">
                    <tr>
                        <th class="text-left p-4">ID</th>
                        <th class="text-left p-4">Nome da Rota</th>
                        <th class="text-left p-4">Cidades</th>
                        <th class="text-left p-4">Status</th>
                        <th class="text-left p-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rotas as $rota)
                    <tr class="border-b border-border hover:bg-secondary/30">
                        <td class="p-4 font-medium">{{ $rota->id }}</td>
                        <td class="p-4">{{ $rota->nome }}</td>
                        <td class="p-4 text-sm text-muted-foreground">
                            <ul class="list-disc ml-5 space-y-1">
                                @foreach ($rota->cidades as $cidade)
                                    <li>{{ $cidade->cidade }} - {{ $cidade->estado }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="p-4">
                            <span class="text-xs px-2 py-1 rounded-full
                                {{ $rota->status === 'ativa' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($rota->status) }}
                            </span>
                        </td>
                        <td class="p-4">
                            <a href="{{ route('rotas.edit', $rota->id) }}"
                                class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-yellow-500 rounded-md hover:bg-yellow-600 transition">
                                <i data-lucide="edit" class="w-4 h-4"></i> Editar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-muted-foreground">Nenhuma rota encontrada.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">
                {{ $rotas->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
