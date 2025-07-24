<?php

namespace App\Http\Controllers;

use App\Models\Rota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RotaController extends Controller
{
    public function index(Request $request)
    {
        $query = Rota::query();

        // Filtros
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('origem', 'like', "%{$search}%")
                  ->orWhere('destino', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->get('status') !== 'all') {
            $query->where('status', $request->get('status'));
        }

        // Ordenação
        $sortField = $request->get('sort_field', 'utilizacoes');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $rotas = $query->paginate(20);

        return response()->json($rotas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origem' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'distancia' => 'required|integer|min:1',
            'tempo_medio' => 'nullable|string|max:50',
            'valor_base' => 'required|numeric|min:0',
            'status' => 'sometimes|in:ativa,inativa'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar se já existe uma rota com a mesma origem e destino
        $rotaExistente = Rota::where('origem', $request->origem)
                             ->where('destino', $request->destino)
                             ->first();

        if ($rotaExistente) {
            return response()->json([
                'error' => 'Já existe uma rota cadastrada para esta origem e destino'
            ], 422);
        }

        $rota = Rota::create($request->all());

        return response()->json($rota, 201);
    }

    public function show($id)
    {
        $rota = Rota::with(['pedidos'])->findOrFail($id);
        return response()->json($rota);
    }

    public function update(Request $request, $id)
    {
        $rota = Rota::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'origem' => 'sometimes|string|max:255',
            'destino' => 'sometimes|string|max:255',
            'distancia' => 'sometimes|integer|min:1',
            'tempo_medio' => 'nullable|string|max:50',
            'valor_base' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:ativa,inativa'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar se a mudança de origem/destino não conflita com rota existente
        if ($request->has('origem') || $request->has('destino')) {
            $origem = $request->get('origem', $rota->origem);
            $destino = $request->get('destino', $rota->destino);
            
            $rotaConflito = Rota::where('origem', $origem)
                                ->where('destino', $destino)
                                ->where('id', '!=', $id)
                                ->first();

            if ($rotaConflito) {
                return response()->json([
                    'error' => 'Já existe uma rota cadastrada para esta origem e destino'
                ], 422);
            }
        }

        $rota->update($request->all());

        return response()->json($rota);
    }

    public function destroy($id)
    {
        $rota = Rota::findOrFail($id);

        // Verificar se a rota está sendo usada em pedidos
        if ($rota->pedidos()->count() > 0) {
            return response()->json([
                'error' => 'Não é possível excluir uma rota que possui pedidos associados'
            ], 422);
        }

        $rota->delete();

        return response()->json(['message' => 'Rota excluída com sucesso']);
    }

    public function ativar($id)
    {
        $rota = Rota::findOrFail($id);
        $rota->update(['status' => 'ativa']);

        return response()->json($rota);
    }

    public function desativar($id)
    {
        $rota = Rota::findOrFail($id);
        $rota->update(['status' => 'inativa']);

        return response()->json($rota);
    }

    public function estatisticas()
    {
        $stats = [
            'total_rotas' => Rota::count(),
            'rotas_ativas' => Rota::where('status', 'ativa')->count(),
            'rota_mais_utilizada' => Rota::orderBy('utilizacoes', 'desc')->first(),
            'distancia_media' => round(Rota::avg('distancia')),
            'valor_medio' => Rota::avg('valor_base')
        ];

        return response()->json($stats);
    }

    public function calcularFrete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rota_id' => 'required|exists:rotas,id',
            'tipo_caminhao_id' => 'required|exists:tipos_caminhao,id',
            'quantidade_toneladas' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $rota = Rota::findOrFail($request->rota_id);
        $tipoCaminhao = \App\Models\TipoCaminhao::findOrFail($request->tipo_caminhao_id);

        $valorBase = $rota->valor_base;
        $valorPorKm = $tipoCaminhao->calcularValorRota($rota->distancia);
        $valorPorTonelada = $request->quantidade_toneladas * 50; // R$ 50 por tonelada (exemplo)

        $valorTotal = $valorBase + $valorPorKm + $valorPorTonelada;
        $valorMotorista = $valorTotal * 0.6; // 60% para o motorista

        return response()->json([
            'valor_base' => $valorBase,
            'valor_por_km' => $valorPorKm,
            'valor_por_tonelada' => $valorPorTonelada,
            'valor_total' => $valorTotal,
            'valor_motorista' => $valorMotorista,
            'margem_empresa' => $valorTotal - $valorMotorista
        ]);
    }
}