@extends('layouts.app')

@section('title', 'Pessoas')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-foreground">Cadastro de {{ ucfirst($tipo) }}s</h1>
            <p class="text-muted-foreground">Gerencie os registros de {{ $tipo }}s do sistema</p>
        </div>
        <a href="{{ route('pessoas.create', $tipo) }}"
            class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Novo {{ $tipo }}
        </a>
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('pessoas.index', $tipo) }}"
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
                        placeholder="Nome, CNPJ, telefone..."
                        class="w-full pl-10 px-3 py-2 border border-border rounded-lg bg-background">
                </div>
            </div>

            <!-- Botões -->
            <div class="flex gap-2 mt-4 md:mt-0">
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Aplicar
                </button>
                <a href="{{ route('pessoas.index', $tipo) }}"
                    class="px-4 py-2 text-sm text-red-600 hover:underline font-medium">Limpar</a>
            </div>
        </div>
    </form>

    <!-- Tabela de Pessoas -->
    <div class="bg-card rounded-lg border border-border">
        <div class="p-6 border-b border-border">
            <h3 class="text-lg font-semibold">📋 Lista de {{ ucfirst($tipo) }}s</h3>
            <p class="text-sm text-muted-foreground">{{ $pessoas->total() }} registros encontrados</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-border">
                    <tr>
                        <th class="text-left p-4">ID</th>
                        <th class="text-left p-4">Nome</th>
                        <th class="text-left p-4">CNPJ</th>
                        <th class="text-left p-4">Email</th>
                        <th class="text-left p-4">Telefone</th>
                        <th class="text-left p-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pessoas as $pessoa)
                    <tr class="border-b border-border hover:bg-secondary/30">
                        <td class="p-4 font-medium">{{ $pessoa->id }}</td>
                        <td class="p-4">{{ $pessoa->nome }}</td>
                        <td class="p-4">{{ $pessoa->cnpj }}</td>
                        <td class="p-4">{{ $pessoa->email }}</td>
                        <td class="p-4">{{ $pessoa->telefone }}</td>
                        <td class="p-4">
                            <a href="{{ route('pessoas.edit', [$tipo, $pessoa]) }}"
                                class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-yellow-500 rounded-md hover:bg-yellow-600 transition">
                                <i data-lucide="edit" class="w-4 h-4"></i> Editar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-muted-foreground">Nenhum registro encontrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">
                {{ $pessoas->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
