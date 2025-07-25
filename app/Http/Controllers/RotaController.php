<?php

namespace App\Http\Controllers;

use App\Models\Rota;
use App\Models\RotaCidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RotaController extends Controller
{
    public function index(Request $request)
    {
        $query = Rota::query();

        // Filtro por nome
        if ($request->has('search')) {
            $search = $request->get('search');

            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                ->orWhereHas('cidades', function ($sub) use ($search) {
                    $sub->where('cidade', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%");
                });
            });
        }


        if ($request->has('status') && $request->get('status') !== 'all') {
            $query->where('status', $request->get('status'));
        }

        $sortField = $request->get('sort_field', 'nome');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $rotas = $query->paginate(10);

        return view('rotas.index', compact('rotas'));
    }

    public function create()
    {
        $rota = new Rota();
        return view('rotas.form', compact('rota'));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'status' => 'required|in:ativa,inativa',
            'cidades' => 'required|array|min:1',
            'cidades.*.cidade' => 'required|string|max:255',
            'cidades.*.estado' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Verificar duplicidade de rota
        if (Rota::where('nome', $request->nome)->exists()) {
            return back()->with('error', 'Já existe uma rota com esse nome.')->withInput();
        }

        $rota = Rota::create([
            'nome' => $request->nome,
            'status' => $request->status,
        ]);

        foreach ($request->cidades as $cidade) {
            $rota->cidades()->create([
                'cidade' => $cidade['cidade'],
                'estado' => $cidade['estado'],
            ]);
        }

        return redirect()->route('rotas.index')->with('success', 'Rota cadastrada com sucesso!');
    }

    public function edit($id)
    {
        $rota = Rota::with('cidades')->findOrFail($id);
        return view('rotas.form', compact('rota'));
    }

    public function update(Request $request, $id)
    {
        $rota = Rota::with('cidades')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'status' => 'required|in:ativa,inativa',
            'cidades' => 'required|array|min:1',
            'cidades.*.cidade' => 'required|string|max:255',
            'cidades.*.estado' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Verifica duplicidade no nome, exceto se for a própria rota
        if (Rota::where('nome', $request->nome)->where('id', '!=', $id)->exists()) {
            return back()->with('error', 'Já existe uma rota com esse nome.')->withInput();
        }

        $rota->update([
            'nome' => $request->nome,
            'status' => $request->status,
        ]);

        // Apaga cidades antigas e recria
        $rota->cidades()->delete();

        foreach ($request->cidades as $cidade) {
            $rota->cidades()->create([
                'cidade' => $cidade['cidade'],
                'estado' => $cidade['estado'],
            ]);
        }

        return redirect()->route('rotas.index')->with('success', 'Rota atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $rota = Rota::findOrFail($id);

        if ($rota->pedidos()->count() > 0) {
            return response()->json([
                'error' => 'Não é possível excluir uma rota com pedidos associados'
            ], 422);
        }

        $rota->delete();

        return response()->json(['message' => 'Rota excluída com sucesso']);
    }

    public function ativar($id)
    {
        Rota::findOrFail($id)->update(['status' => 'ativo']);
        return redirect()->route('rotas.index')->with('success', 'Rota ativada com sucesso!');
    }

    public function desativar($id)
    {
        Rota::findOrFail($id)->update(['status' => 'inativo']);
        return redirect()->route('rotas.index')->with('success', 'Rota desativada com sucesso!');
    }
}
