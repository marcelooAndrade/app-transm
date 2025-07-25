<?php

namespace App\Http\Controllers;

use App\Models\PedidoLogistica;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $statusClasses = [
            'Pendente' => 'bg-amber-100 text-amber-800',
            'Liberado' => 'bg-emerald-100 text-emerald-800',
            'Em Trânsito' => 'bg-sky-100 text-sky-800',
            'Entregue' => 'bg-neutral-200 text-neutral-800',
            'Cancelado' => 'bg-rose-100 text-rose-800',
        ];
        $query = PedidoLogistica::with(['fornecedor', 'cliente', 'rota']);

        // Filtros
        if ($request->has('search')) {
            $search = $request->get('search');

            $query->where(function ($q) use ($search) {
                $q->where('numero_pedido', 'like', "%{$search}%")
                    ->orWhere('representante', 'like', "%{$search}%")
                    ->orWhere('cidade', 'like', "%{$search}%")
                    ->orWhere('estado', 'like', "%{$search}%")
                    ->orWhere('cliente', 'like', "%{$search}%")
                    ->orWhere('industria', 'like', "%{$search}%")
                    ->orWhere('data_pedido', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->get('status') !== 'all') {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('data_inicio') && $request->has('data_fim')) {
            $query->whereBetween('created_at', [
                $request->get('data_inicio'),
                $request->get('data_fim')
            ]);
        }

        // Ordenação
        $sortField = $request->get('sort_field', 'cliente');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $pedidos = $query->paginate(10);


        return view('pedidos.index', compact('pedidos', 'statusClasses'));
    }

    public function create()
    {
        $clientes = Pessoa::orderBy('nome')->get();
        return view('pedidos.form', compact('clientes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'cliente_id' => 'required|exists:clientes,id',
            'quantidade_toneladas' => 'required|numeric|min:0',
            'quantidade_paletes' => 'required|integer|min:0',
            'rota_id' => 'required|exists:rotas,id',
            'tipo_caminhao_id' => 'required|exists:tipos_caminhao,id',
            'valor_frete' => 'required|numeric|min:0',
            'valor_frete_motorista' => 'required|numeric|min:0',
            'data_entrega_prevista' => 'required|date',
            'observacoes' => 'nullable|string',
            'status'      => 'nullable|in:Pendente,Liberado, Em Trânsito,Entregue,Cancelado',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Gerar número do pedido
        $numeroPedido = 'PED-' . str_pad(PedidoLogistica::count() + 1, 6, '0', STR_PAD_LEFT);

        $pedido = PedidoLogistica::create(array_merge($request->all(), [
            'numero_pedido' => $numeroPedido,
            'status' => 'pendente'
        ]));

        // Incrementar utilização da rota e tipo de caminhão
        $pedido->rota->incrementarUtilizacao();
        $pedido->tipoCaminhao->incrementarUtilizacao();

        return response()->json($pedido->load(['fornecedor', 'cliente', 'rota']), 201);
    }

    public function edit($id)
    {
        $pedido = PedidoLogistica::with(['fornecedor', 'cliente', 'rota'])->findOrFail($id);
        $clientes = Pessoa::orderBy('nome')->get();

        return view('pedidos.form', compact('pedido', 'clientes'));
    }

    public function update(Request $request, $id)
    {
        $pedido = PedidoLogistica::findOrFail($id);

        $validated = $request->validate([
            'representante'       => 'nullable|string|max:255',
            'data_pedido'         => 'nullable|date',
            'cliente_id'             => 'nullable|string|max:255',
            'numero_pedido'       => 'nullable|string|max:255',
            'codigo_produto'      => 'nullable|string|max:255',
            'descricao_produto'   => 'nullable|string|max:255',
            'industria'           => 'nullable|string|max:255',
            'qtd_pallets'         => 'nullable|integer|min:0',
            'tipo_produto'        => 'nullable|string|max:255',
            'total_m2'            => 'nullable|numeric|min:0',
            'peso_total'          => 'nullable|numeric|min:0',
            'cidade'              => 'nullable|string|max:255',
            'estado'              => 'nullable|string|max:2',
            'status'              => 'nullable|in:Pendente,Liberado, Em Trânsito,Entregue,Cancelado',
        ]);

        $pedido->update($validated);

        return redirect()
            ->route('pedidos.index')
            ->with('success', 'Pedido atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $pedido = PedidoLogistica::findOrFail($id);
        $pedido->delete();

        return response()->json(['message' => 'Pedido excluído com sucesso']);
    }

    public function marcarComoEntregue($id)
    {
        $pedido = PedidoLogistica::findOrFail($id);
        $pedido->marcarComoEntregue();

        return response()->json($pedido);
    }

    public function gerarOrdemServico($id)
    {
        $pedido = PedidoLogistica::with(['fornecedor', 'cliente', 'rota'])->findOrFail($id);

        // Aqui seria implementada a geração do PDF da ordem de serviço
        $ordemServico = [
            'numero_os' => 'OS-' . $pedido->id,
            'pedido' => $pedido,
            'data_geracao' => now(),
            'instrucoes' => 'Instruções específicas para o motorista...'
        ];

        return response()->json($ordemServico);
    }

    public function importarXML(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'arquivo' => 'required|file|mimes:xml'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Aqui seria implementada a lógica de importação do XML
        // Por enquanto, retornamos uma resposta simulada

        return response()->json([
            'message' => 'Arquivo XML importado com sucesso',
            'pedidos_importados' => 5,
            'pedidos_com_erro' => 0
        ]);
    }

    public function importarPDF(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'arquivo' => 'required|file|mimes:pdf'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Aqui seria implementada a lógica de importação do PDF
        // Por enquanto, retornamos uma resposta simulada

        return response()->json([
            'message' => 'Arquivo PDF processado com sucesso',
            'dados_extraidos' => 8,
            'pedidos_criados' => 3
        ]);
    }
}
