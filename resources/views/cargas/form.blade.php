@extends('layouts.app')

@section('title', $carga->exists ? 'Editar Carga' : 'Nova Carga')

@section('content')
<div class="max-w-5xl mx-auto bg-white p-6 rounded shadow border space-y-6 animate-fade-in">
    <h1 class="text-xl font-bold">
        {{ $carga->exists ? 'Editar Carga' : 'Nova Carga' }}
    </h1>

    <form method="POST" action="{{ $carga->exists ? route('cargas.update', $carga) : route('cargas.store') }}">
        @csrf
        @if($carga->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium block mb-1" for="nome">Nome da Carga</label>
                <input type="text" name="nome" id="nome" value="{{ old('nome', $carga->nome) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm" required>
            </div>

            <div>
                <label class="text-sm font-medium block mb-1" for="rota_id">Rota</label>
                <select name="rota_id" id="rota_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm" required>
                    <option value="">Selecione uma rota</option>
                    @foreach($rotas as $rota)
                    <option value="{{ $rota->id }}" {{ old('rota_id', $carga->rota_id) == $rota->id ? 'selected' : ''
                        }}>
                        {{ $rota->nome }} -
                        {{ $rota->cidades->pluck('cidade')->join(', ') }}/{{
                        $rota->cidades->pluck('estado')->unique()->join(', ') }}
                    </option>
                    @endforeach

                </select>
            </div>
        </div>

        <hr class="my-6">

        <div>
            <h2 class="text-lg font-semibold mb-3">📦 Pedidos Liberados</h2>
            <p class="text-sm text-muted-foreground mb-4">Selecione os pedidos que deseja incluir nesta carga</p>
            <div class="border rounded-lg max-h-[300px] overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 w-12"></th>
                            <th class="p-3 text-left">Pedido</th>
                            <th class="p-3 text-left">Cliente</th>
                            <th class="p-3 text-left">Cidade</th>
                            <th class="p-3 text-left">Peso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pedidos as $pedido)
                        <tr class="border-t">
                            <td class="p-3 text-center">
                                <input type="checkbox" name="pedido_ids[]" value="{{ $pedido->id }}" {{
                                    in_array($pedido->id, old('pedido_ids', $cargaPedidos ?? [])) ? 'checked' : '' }}>
                            </td>
                            <td class="p-3">#{{ $pedido->numero_pedido }}</td>
                            <td class="p-3">{{ $pedido->cliente }}</td>
                            <td class="p-3">{{ $pedido->cidade }}/{{ $pedido->estado }}</td>
                            <td class="p-3">{{ $pedido->peso_total }} kg</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-muted-foreground">Nenhum pedido liberado
                                disponível.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex gap-4">
            <button type="submit"
                class="bg-emerald-600 px-4 py-2 rounded text-white font-semibold hover:bg-emerald-700">
                Salvar Carga
            </button>
            <a href="{{ route('cargas.index') }}" class="text-gray-600 hover:underline">Cancelar</a>
        </div>
    </form>
</div>
@endsection
