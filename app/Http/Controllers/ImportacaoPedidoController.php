<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use App\Models\PedidoLogistica;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;


class ImportacaoPedidoController extends Controller
{
    public function index()
    {
        return view('importar.index');
    }

    public function upload(Request $request)
    {

        $request->validate([
            'arquivo' => 'required|file|mimes:pdf',
        ]);

        // Extrai texto do PDF
        $pdfFile = $request->file('arquivo');
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfFile->getPathname());
        $texto = $pdf->getText();

        // Envia à OpenAI para estruturação dos dados
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um extrator de dados de pedidos de logística. Extraia os dados do PDF e retorne apenas o JSON puro — sem comentários, explicações ou marcações Markdown. O JSON deve conter uma lista de pedidos com os seguintes campos por item: representante, data_pedido, cliente, numero_pedido, codigo_produto, descricao_produto, industria, qtd_pallets, tipo_produto, total_m2, peso_total, cidade, estado.

                ⚠️ Traga apenas os pedidos da empresa TRANSM TRANSPORTES LTDA.
                ⚠️ Não inclua texto adicional ou explicações. Apenas JSON.
                ⚠️ O campo "codigo_produto" é obrigatório. Caso não esteja presente no PDF, crie um código temporário como "TEMP_001", "TEMP_002" etc.


                '],
                ['role' => 'user', 'content' => $texto],
            ],
        ]);

        $conteudo = trim($response->choices[0]->message->content);

        // Remove qualquer texto antes ou depois do JSON válido (ex: “Segue os dados...”)
        if (strpos($conteudo, '[') !== false) {
            $conteudo = substr($conteudo, strpos($conteudo, '['));
            $conteudo = substr($conteudo, 0, strrpos($conteudo, ']') + 1);
        }

        // Remove blocos de markdown
        $conteudo = trim($conteudo);
        if (str_starts_with($conteudo, '```json')) {
            $conteudo = preg_replace('/^```json\s*/', '', $conteudo);
            $conteudo = preg_replace('/\s*```$/', '', $conteudo);
        }

        $pedidos = json_decode($conteudo, true);

        if (!is_array($pedidos)) {
            Log::warning('Erro ao decodificar JSON da OpenAI', ['conteudo' => $conteudo]);

            return response()->json([
                'erro' => 'Resposta da IA não é um JSON válido após limpeza.',
                'resposta' => $conteudo,
            ], 422);
        }

        $importados = [];


        foreach ($pedidos as $pedido) {

            $dataBruto = $pedido['data_pedido'] ?? null;

            try {
                $dataPedido = Carbon::createFromFormat('d/m/Y H:i:s', $dataBruto)->format('Y-m-d');
            } catch (\Exception $e1) {
                try {
                    $dataPedido = Carbon::createFromFormat('d/m/Y', $dataBruto)->format('Y-m-d');
                } catch (\Exception $e2) {
                    $dataPedido = '1900-01-01'; // fallback padrão
                }
            }

            $numero = $pedido['numero_pedido'] ?? null;
            $codigo = !empty($pedido['codigo_produto'])
                ? $pedido['codigo_produto']
                : 'TEMP_' . substr(md5($numero . microtime()), 0, 6);

            if (!$numero) {
                continue;
            }

            $existe = PedidoLogistica::where('numero_pedido', $numero)
                ->where('codigo_produto', $codigo)
                ->exists();

            $peso = $pedido['peso_total'] ?? 0;
            $peso = str_replace(['.', ','], ['', '.'], $peso);

            $m2 = $pedido['total_m2'] ?? 0;
            $m2 = str_replace(['.', ','], ['', '.'], $m2);

            $qtdPallets = is_numeric(str_replace(',', '.', $pedido['qtd_pallets'] ?? ''))
                ? (int) floatval(str_replace(',', '.', $pedido['qtd_pallets']))
                : 0;

            if (!$existe) {
                PedidoLogistica::create([
                    'representante'     => $pedido['representante'] ?? 'Não informado',
                    'data_pedido'       => $dataPedido,
                    'cliente'           => $pedido['cliente'] ?? 'Não informado',
                    'numero_pedido'     => $numero,
                    'codigo_produto'    => $codigo,
                    'descricao_produto' => $pedido['descricao_produto'] ?? 'Não informado',
                    'industria'         => $pedido['industria'] ?? 'Manual',
                    'qtd_pallets'       => $qtdPallets,
                    'tipo_produto'      => $pedido['tipo_produto'] ?? 'CERAMICA/PISO',
                    'total_m2'          => is_numeric($m2) ? $m2 : 0,
                    'peso_total'        => is_numeric($peso) ? $peso : 0,
                    'cidade'            => $pedido['cidade'] ?? 'Não informado',
                    'estado'            => $pedido['estado'] ?? 'NA',
                    'status'            => 'importado',
                ]);

                $importados[] = "{$numero} - {$codigo}";
            }
        }

        return redirect()->route('pedidos.index')->with('success', count($importados) . ' pedidos importados com sucesso!');
    }

    public function scraping(Request $request)
    {

        $importados = [];

        foreach ($request->pedidos as $data) {
            // Verifica se o pedido já foi importado
            if (!PedidoLogistica::where('numero_pedido', $data['numero_pedido'])->exists()) {

                // Monta os dados do pedido
                $pedidoData = [
                    'cliente'     => $data['cliente'],
                    'numero_pedido'  => $data['numero_pedido'],
                    'codigo_produto' => $data['codigo_produto'],
                    'descricao_produto' => $data['descricao_produto'],
                    'industria'      => $data['industria'],
                    'qtd_pallets'    => $data['qtd_pallets'],
                    'tipo_produto'   => $data['tipo_produto'],
                    'total_m2'       => $data['total_m2'],
                    'peso_total'     => $data['peso_total'],
                    'cidade'         => $data['cidade'],
                    'estado'         => $data['estado'],
                    'data_pedido'    => $data['data_pedido'] ?? now(),
                    'representante'  => $data['representante'] ?? 'Desconhecido',
                    'status'         => 'Pendente',
                ];

                $importados[] = PedidoLogistica::create($pedidoData);
            }
        }

        return response()->json([
            'message' => count($importados)
                ? count($importados) . ' pedidos importados com sucesso.'
                : 'Nenhum novo pedido foi importado. Todos já existem.'
        ]);
    }
}
