@extends('layouts.app') {{-- ou o nome do seu layout principal --}}

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow border space-y-6">
    <h1 class="text-xl font-bold">
        {{ $pessoa->exists ? "Editar $tipo" : "Novo $tipo" }}
    </h1>

    <form method="POST" action="{{ $pessoa->exists ? route('pessoas.update', [$tipo, $pessoa]) : route('pessoas.store', $tipo) }}">
        @csrf
        @if($pessoa->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Nome --}}
            <div>
                <label class="text-sm font-medium block mb-1" for="nome">Nome</label>
                <input type="text" name="nome" id="nome" value="{{ old('nome', $pessoa->nome) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm" required>
            </div>

            {{-- CNPJ --}}
            <div>
                <label class="text-sm font-medium block mb-1" for="cnpj">CNPJ</label>
                <input type="text" name="cnpj" id="cnpj" value="{{ old('cnpj', $pessoa->cnpj) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm">
            </div>

            {{-- Email --}}
            <div>
                <label class="text-sm font-medium block mb-1" for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $pessoa->email) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm">
            </div>

            {{-- Telefone --}}
            <div>
                <label class="text-sm font-medium block mb-1" for="telefone">Telefone</label>
                <input type="text" name="telefone" id="telefone" value="{{ old('telefone', $pessoa->telefone) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm">
            </div>

            {{-- Endereço --}}
            <div class="md:col-span-2">
                <label class="text-sm font-medium block mb-1" for="endereco">Endereço</label>
                <textarea name="endereco" id="endereco"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm h-24 resize-none">{{ old('endereco', $pessoa->endereco) }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex gap-4">
            <button type="submit"
                class="bg-emerald-600 px-4 py-2 rounded text-white font-semibold hover:bg-emerald-700">
                Salvar
            </button>
            <a href="{{ route('pessoas.index', $tipo) }}" class="text-gray-600 hover:underline">Cancelar</a>
        </div>
    </form>
</div>
@endsection
