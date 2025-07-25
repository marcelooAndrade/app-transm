<?php

namespace App\Http\Controllers;

use App\Models\Rota;
use App\Models\Carga;
use Illuminate\Http\Request;
use App\Models\PedidoLogistica;

class CargaController extends Controller
{
    public function index(Request $request)
    {
        $query = Carga::with('rota');

        // 🔍 Filtro por nome da carga ou dados da rota
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('nome', 'like', "%{$search}%")
                  ->orWhereHas('rota', function ($q) use ($search) {
                      $q->where('nome', 'like', "%{$search}%")
                        ->orWhere('cidade', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%");
                  });
        }

        // 📍 Filtro por rota
        if ($request->filled('rota_id')) {
            $query->where('rota_id', $request->rota_id);
        }

        // 🗓️ Filtro por período
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $rotas = Rota::orderBy('nome')->get();
        $cargas = $query->latest()->paginate(10);

        return view('cargas.index', compact('cargas', 'rotas'));
    }

    public function create()
    {
        $carga = new Carga();
        $rotas = Rota::with('cidades')->where('status', 'ativa')->get();
        $pedidos = PedidoLogistica::where('status', 'Liberado')->get();
        $cargaPedidos = [];

        return view('cargas.form', compact('carga', 'rotas', 'pedidos', 'cargaPedidos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'rota_id' => 'required|exists:rotas,id',
            'pedido_ids' => 'required|array|min:1',
            'pedido_ids.*' => 'exists:pedido_logisticas,id',
        ]);

        $carga = Carga::create([
            'nome' => $request->nome,
            'rota_id' => $request->rota_id,
        ]);

        $carga->pedidos()->attach($request->pedido_ids);

        return redirect()->route('cargas.index')->with('success', 'Carga criada com sucesso!');
    }

    public function edit($id)
    {
        $carga = Carga::with('pedidos')->findOrFail($id);
        $rotas = Rota::with('cidades')->where('status', 'ativa')->get();
        $pedidos = PedidoLogistica::where('status', 'Liberado')->orWhereIn('id', $carga->pedidos->pluck('id'))->get();
        $cargaPedidos = $carga->pedidos->pluck('id')->toArray();

        return view('cargas.form', compact('carga', 'rotas', 'pedidos', 'cargaPedidos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'rota_id' => 'required|exists:rotas,id',
            'pedido_ids' => 'required|array|min:1',
            'pedido_ids.*' => 'exists:pedido_logisticas,id',
        ]);

        $carga = Carga::findOrFail($id);
        $carga->update([
            'nome' => $request->nome,
            'rota_id' => $request->rota_id,
        ]);

        $carga->pedidos()->sync($request->pedido_ids);

        return redirect()->route('cargas.index')->with('success', 'Carga atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $carga = Carga::findOrFail($id);
        $carga->pedidos()->detach();
        $carga->delete();

        return redirect()->route('cargas.index')->with('success', 'Carga excluída com sucesso!');
    }
}
