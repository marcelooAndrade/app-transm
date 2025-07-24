@extends('layouts.app')

@section('title', isset($pedido) ? 'Editar Pedido' : 'Novo Pedido')

@section('content')
<div class="max-w-6xl mx-auto p-6 space-y-6 animate-fade-in">
    <!-- Cabeçalho -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                {{ isset($pedido) ? 'Editar Pedido #' . $pedido->id : 'Novo Pedido' }}
            </h1>
            <p class="text-gray-500">
                {{ isset($pedido) ? 'Atualize os dados do pedido de frete.' : 'Cadastre um novo pedido no sistema.' }}
            </p>
        </div>
        <a href="{{ route('pedidos.index') }}" class="text-sm text-gray-500 hover:underline">← Voltar</a>
    </div>

    <!-- Formulário -->
    <form method="POST" action="{{ isset($pedido) ? route('pedidos.update', $pedido->id) : route('pedidos.store') }}"
        class="bg-white p-6 rounded-lg shadow border space-y-6">
        @csrf
        @if(isset($pedido))
        @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- <div>
                <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                <select name="cliente_id" id="cliente_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecione um cliente</option>
                    @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" {{ old('cliente_id', $pedido->cliente_id ?? '') == $cliente->id ?
                        'selected' : '' }}>
                        {{ $cliente->nome }}
                    </option>
                    @endforeach
                </select>
            </div> --}}


            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                <input type="text" name="cidade" value="{{ old('cidade', $pedido->cidade ?? '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <input type="text" name="estado" value="{{ old('estado', $pedido->estado ?? '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Peso Total (kg)</label>
                <input type="number" name="peso_total" value="{{ old('peso_total', $pedido->peso_total ?? '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Qtd. Paletes</label>
                <input type="number" name="qtd_pallets" value="{{ old('qtd_pallets', $pedido->qtd_pallets ?? '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Produto</label>
                <input type="text" name="tipo_produto" value="{{ old('tipo_produto', $pedido->tipo_produto ?? '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Área Total (m²)</label>
                <input type="number" step="0.01" name="total_m2" value="{{ old('total_m2', $pedido->total_m2 ?? '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['Pendente', 'Em Trânsito', 'Entregue', 'Cancelado'] as $status)
                    <option value="{{ $status }}" {{ (old('status', $pedido->status ?? '') === $status) ? 'selected' :
                        '' }}>
                        {{ $status }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data do Pedido</label>
                <input type="date" name="data_pedido"
                    value="{{ old('data_pedido', isset($pedido) ? \Carbon\Carbon::parse($pedido->data_pedido)->format('Y-m-d') : '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Botão -->
        <div class="pt-4">
            <button type="submit"
                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                {{ isset($pedido) ? 'Atualizar Pedido' : 'Cadastrar Pedido' }}
            </button>
        </div>
    </form>
</div>
@endsection
