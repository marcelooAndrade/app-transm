<?php

namespace App\Http\Controllers;

use App\Models\TipoCaminhao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoCaminhaoController extends Controller
{
    public function index(Request $request)
    {
        $query = TipoCaminhao::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        $tipos = $query->orderBy('utilizacoes', 'desc')->paginate(20);
        return response()->json($tipos);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255|unique:tipos_caminhao',
            'descricao' => 'nullable|string',
            'capacidade_toneladas' => 'required|numeric|min:0',
            'capacidade_paletes' => 'required|integer|min:0',
            'valor_km' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tipo = TipoCaminhao::create($request->all());
        return response()->json($tipo, 201);
    }

    public function update(Request $request, $id)
    {
        $tipo = TipoCaminhao::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|string|max:255|unique:tipos_caminhao,nome,' . $id,
            'descricao' => 'nullable|string',
            'capacidade_toneladas' => 'sometimes|numeric|min:0',
            'capacidade_paletes' => 'sometimes|integer|min:0',
            'valor_km' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:ativo,inativo'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tipo->update($request->all());
        return response()->json($tipo);
    }

    public function destroy($id)
    {
        $tipo = TipoCaminhao::findOrFail($id);
        
        if ($tipo->pedidos()->count() > 0) {
            return response()->json(['error' => 'Tipo em uso por pedidos'], 422);
        }

        $tipo->delete();
        return response()->json(['message' => 'Tipo excluído com sucesso']);
    }
}