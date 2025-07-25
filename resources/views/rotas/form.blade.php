@extends('layouts.app')

@section('title', $rota->exists ? 'Editar Rota' : 'Nova Rota')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow border space-y-6">
    <h1 class="text-xl font-bold">
        {{ $rota->exists ? 'Editar Rota' : 'Nova Rota' }}
    </h1>

    <form method="POST" action="{{ $rota->exists ? route('rotas.update', $rota) : route('rotas.store') }}">
        @csrf
        @if($rota->exists) @method('PUT') @endif

        <!-- Nome e Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium block mb-1" for="nome">Nome da Rota</label>
                <input type="text" name="nome" id="nome" value="{{ old('nome', $rota->nome) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm" required>
            </div>
            <div>
                <label class="text-sm font-medium block mb-1" for="status">Status</label>
                <select name="status" id="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm">
                    <option value="ativa" {{ old('status', $rota->status) === 'ativa' ? 'selected' : '' }}>Ativa</option>
                    <option value="inativa" {{ old('status', $rota->status) === 'inativa' ? 'selected' : '' }}>Inativa</option>
                </select>
            </div>
        </div>

        <!-- Cidades da Rota -->
        <div class="mt-6 space-y-4">
            <h2 class="text-lg font-semibold">Cidades</h2>
            <div id="cidades-container" class="space-y-4">
                @php
                    $cidades = old('cidades', $rota->cidades ?? [ ['cidade' => '', 'estado' => ''] ]);
                @endphp

                @foreach($cidades as $index => $cidade)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 cidade-item">
                    <input type="text" name="cidades[{{ $index }}][cidade]" placeholder="Cidade"
                        value="{{ $cidade['cidade'] ?? '' }}"
                        class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm" required>

                    <input type="text" name="cidades[{{ $index }}][estado]" placeholder="Estado"
                        value="{{ $cidade['estado'] ?? '' }}"
                        class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm" required>

                    <button type="button" onclick="removerCidade(this)"
                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 self-end">
                        Remover
                    </button>
                </div>
                @endforeach
            </div>

            <button type="button" onclick="adicionarCidade()"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Adicionar Cidade
            </button>
        </div>

        <div class="mt-6 flex gap-4">
            <button type="submit"
                class="bg-emerald-600 px-4 py-2 rounded text-white font-semibold hover:bg-emerald-700">
                Salvar
            </button>
            <a href="{{ route('rotas.index') }}" class="text-gray-600 hover:underline">Cancelar</a>
        </div>
    </form>
</div>

<script>
    function adicionarCidade() {
        const container = document.getElementById('cidades-container');
        const index = container.querySelectorAll('.cidade-item').length;

        const html = `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 cidade-item">
            <input type="text" name="cidades[${index}][cidade]" placeholder="Cidade"
                class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm" required>
            <input type="text" name="cidades[${index}][estado]" placeholder="Estado"
                class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm" required>
            <button type="button" onclick="removerCidade(this)"
                class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 self-end">
                Remover
            </button>
        </div>`;

        container.insertAdjacentHTML('beforeend', html);
    }

    function removerCidade(button) {
        button.closest('.cidade-item').remove();
    }
</script>
@endsection
