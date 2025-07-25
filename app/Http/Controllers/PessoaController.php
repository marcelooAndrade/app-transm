<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use Illuminate\Http\Request;

class PessoaController extends Controller
{
    public function index(Request $request, $tipo)
    {
        $query = Pessoa::where('tipo', $tipo);

        // Filtro de busca livre (nome, cnpj, telefone)
        if ($request->filled('search')) {
            $busca = $request->input('search');
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('cnpj', 'like', "%{$busca}%")
                    ->orWhere('telefone', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%");
            });
        }

        $pessoas = $query->paginate(10);

        return view('pessoas.index', compact('pessoas', 'tipo'));
    }


    public function create($tipo)
    {
        return view('pessoas.form', ['pessoa' => new Pessoa(), 'tipo' => $tipo]);
    }

    public function store(Request $request, $tipo)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|unique:pessoas,cnpj',
            'email' => 'nullable|email',
            'telefone' => 'nullable|string|max:255',
            'endereco' => 'nullable|string',
        ]);

        $data['tipo'] = $tipo;
        Pessoa::create($data);

        return redirect()->route('pessoas.index', $tipo)->with('success', ucfirst($tipo) . ' cadastrado com sucesso.');
    }

    public function edit($tipo, Pessoa $pessoa)
    {
        return view('pessoas.form', compact('pessoa', 'tipo'));
    }

    public function update(Request $request, $tipo, Pessoa $pessoa)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|unique:pessoas,cnpj,' . $pessoa->id,
            'email' => 'nullable|email',
            'telefone' => 'nullable|string|max:255',
            'endereco' => 'nullable|string',
        ]);

        $pessoa->update($data);

        return redirect()->route('pessoas.index', $tipo)->with('success', ucfirst($tipo) . ' atualizado com sucesso.');
    }

    public function destroy($tipo, Pessoa $pessoa)
    {
        $pessoa->delete();
        return back()->with('success', ucfirst($tipo) . ' removido com sucesso.');
    }
}
