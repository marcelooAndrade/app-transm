<?php

namespace App\Scrapers;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\PedidoLogistica;
use Carbon\Carbon;

class IncopisosScraper
{
    public static function coletar(): array
    {
        $client = new Client([
            'base_uri' => 'http://portal.grupoincopisos.com.br',
            'cookies' => true,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0',
            ],
        ]);

        // 1. Login
        $response = $client->post('/login.php', [
            'form_params' => [
                'form_user_id' => '47528089000127',
                'form_password' => 'T18663-16',
                'sistema' => 'TRP',
                'id_cia' => '8',
                'submit' => 'Acessar',
            ]
        ]);

        $html = (string) $response->getBody();

        // 2. Captura o SSID
        $crawler = new Crawler($html);
        $ssid = null;

        foreach ($crawler->filter('frame') as $frame) {
            $src = $frame->getAttribute('src');
            if (preg_match('/ssid=([a-zA-Z0-9_]+)/', $src, $matches)) {
                $ssid = $matches[1];
                break;
            }
        }

        if (!$ssid) {
            throw new \Exception("❌ SSID não encontrado após login.");
        }

        // 3. Acessa os dados da planilha
        $response = $client->get('/montagem/topo_reserva.php', [
            'query' => [
                'cod_transportadora' => '47528089000127',
                'cod_motorista' => '',
                'fmc_data' => '',
                'fmc_obs' => '',
                'ssid' => $ssid,
            ]
        ]);

        $html = (string) $response->getBody();

        // 4. Parse da tabela com DOM
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $rows = $xpath->query("//table//tr[td]");
        $pedidos = [];

        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName('td');

            // Ignora linhas com menos de, por exemplo, 5 colunas (mínimo seguro)
            if ($cols->length < 5) continue;

            try {
                $get = fn($i) => $cols->item($i)?->nodeValue ?? '';

                $cidadeUf = explode(' / ', trim($get(4)));
                $cidade = $cidadeUf[0] ?? 'ND';
                $estado = $cidadeUf[1] ?? 'ND';

                $pedido = [
                    'representante' => 'Não tem',
                    'data_pedido' => \Carbon\Carbon::createFromFormat('d/m/Y', trim($get(1)))->toDateString(),
                    'cliente' => trim($get(3)),
                    'numero_pedido' => trim($get(0)),
                    'codigo_produto' => '',
                    'descricao_produto' => trim($get(5)),
                    'industria' => 'Incopisos',
                    'qtd_pallets' => (int) floatval(str_replace(',', '.', $get(13))),
                    'tipo_produto' => 'CERAMICA/PISO',
                    'total_m2' => floatval(str_replace(',', '.', $get(8))),
                    'peso_total' => floatval(str_replace(',', '.', $get(12))),
                    'cidade' => $cidade,
                    'estado' => $estado,
                    'status' => 'Pendente',
                ];

                $pedidos[] = $pedido;
            } catch (\Throwable $e) {
                continue;
            }
        }


        return $pedidos;
    }
}
