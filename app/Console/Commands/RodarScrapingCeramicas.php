<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\PedidoLogistica;
use App\Models\Pessoa;
use App\Scrapers\CedasaScraper;
use Illuminate\Console\Command;
use App\Scrapers\IncopisosScraper;
use App\Scrapers\VillagresScraper;
use OpenAI\Laravel\Facades\OpenAI;
use App\Scrapers\DeltaCeramicaScraper;
use App\Scrapers\CeramicaCecafiScraper;
use App\Scrapers\CeramicaAlmeidaScraper;
use App\Scrapers\NovaPorcelanatoScraper;
use App\Scrapers\TransportadoraCcfScraper;

class RodarScrapingCeramicas extends Command
{
    protected $signature = 'scraping:ceramicas';
    protected $description = 'Executa scraping das cerâmicas e envia para API';

    public function handle()
    {
        $coletores = [
            'Ceramica Almeida' => CeramicaAlmeidaScraper::class,
            'Transportadora CCF' => TransportadoraCcfScraper::class,
            'Cedasa' => CedasaScraper::class,
            'Delta Cerâmica' => DeltaCeramicaScraper::class,
            'Incopisos' => IncopisosScraper::class,
            'Nova Porcelanato' => NovaPorcelanatoScraper::class,
            'Villagres' => VillagresScraper::class,
            'Cerâmica Cecafi' => CeramicaCecafiScraper::class,
        ];


        $pedidos = [];
        echo "📦 Iniciando coletas de pedidos...\n";
        flush();

        foreach ($coletores as $nomeIndustria => $scraperClass) {
            try {
                $scraper = new $scraperClass();
                $resultado = $scraper->coletar();
                $pedidos = array_merge($pedidos, $resultado);
                echo "✅ {$nomeIndustria} retornou " . count($resultado) . " pedidos.\n";
                flush();
            } catch (\Throwable $e) {
                echo "❌ Erro em {$nomeIndustria}: " . $e->getMessage() . "\n";
            }
        }


        if (count($pedidos) === 0) {
            echo "⚠️ Nenhum pedido coletado.\n";
            flush();
            return 0;
        }

        try {

            $clientes = Pessoa::pluck('nome')->toArray();
            $clientesCadastrados = implode(', ', $clientes);

            foreach ($pedidos as &$p) {
                // 1. Formatar data
                if (!empty($p['data_pedido']) && str_contains($p['data_pedido'], '/')) {
                    $p['data_pedido'] = \Carbon\Carbon::createFromFormat('d/m/Y', $p['data_pedido'])->toDateString();
                }

                // 2. Normaliza nome do cliente
                $p['cliente'] = ucwords(strtolower($p['cliente']));

                // 3. Verifica duplicidade de pedido
                $existe = \App\Models\PedidoLogistica::where('numero_pedido', $p['numero_pedido'])
                    ->where('industria', $p['industria'])
                    ->exists();

                if ($existe) {
                    continue;
                }

                // 4. Verifica cliente existente
                $clienteExiste = Pessoa::where('nome', $p['cliente'])->first();

                if (!$clienteExiste) {
                    // Pega todos os nomes já cadastrados
                    $nomes = Pessoa::pluck('nome')->toArray();
                    $clientesCadastrados = implode(', ', $nomes);

                    // Consulta à OpenAI para sugerir nome semelhante
                    $resposta = OpenAI::chat()->create([
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            ['role' => 'system', 'content' => 'Você é um assistente que detecta nomes semelhantes de clientes em uma base.'],
                            ['role' => 'user', 'content' => "Os nomes cadastrados são: {$clientesCadastrados}. O nome '{$p['cliente']}' já aparece como uma variação semelhante de algum nome? Retorne apenas o nome correspondente, se houver."],
                        ],
                    ]);

                    $respostaTexto = $resposta['choices'][0]['message']['content'] ?? '';

                    preg_match("/nome '(.*?)'/i", $respostaTexto, $matches);
                    $sugerido = $matches[1] ?? 'não encontrado';
                    if (strtolower($sugerido) !== 'não encontrado') {
                        // Substitui pelo nome já existente
                        $p['cliente'] = $sugerido;
                    } else {
                        // Cadastra novo cliente
                        Pessoa::create([
                            'nome' => $p['cliente'],
                        ]);
                    }
                }
            }

            // Filtra apenas os pedidos que não foram ignorados por duplicidade
            $pedidosParaInserir = array_filter($pedidos, function ($p) {
                return !\App\Models\PedidoLogistica::where('numero_pedido', $p['numero_pedido'])
                    ->where('industria', $p['industria'])
                    ->exists();
            });

            PedidoLogistica::insert($pedidosParaInserir);
            echo count($pedidosParaInserir) . " pedidos inseridos com sucesso!\n";
            flush();
        } catch (\Throwable $e) {
            echo "❌ Erro ao salvar os pedidos: " . $e->getMessage() . "\n";
        }


        return 0;
    }
}
