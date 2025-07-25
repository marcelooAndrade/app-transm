<?php
namespace App\Scrapers;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\PedidoLogistica;
use Carbon\Carbon;

class CedasaScraper
{
    public static function coletar(): array
    {
        $client = new Client([
            'base_uri' => 'http://186.201.210.130:8800',
            'cookies' => true,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0',
            ],
        ]);

        // 1. Login
        $response = $client->post('/pedidos/login.php?id_cia=2', [
            'form_params' => [
                'form_user_id' => '47528089000127',
                'form_password' => 'TRA475',
                'sistema' => 'TRP',
                'submit' => 'Continuar',
            ]
        ]);

        $html = (string) $response->getBody();

        // 2. Captura o SSID a partir do src do frame
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
        $response = $client->get('/pedidos/montagem/topo_reserva.php', [
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
            if ($cols->length < 14) continue;

            try {
                $cidadeUf = explode(' / ', trim($cols[4]->nodeValue));
                $cidade = $cidadeUf[0] ?? 'ND';
                $estado = $cidadeUf[1] ?? 'ND';

                $pedido = [
                    'representante' => 'Não tem',
                    'data_pedido' => Carbon::createFromFormat('d/m/Y', trim($cols[1]->nodeValue))->toDateString(),
                    'cliente' => trim($cols[3]->nodeValue),
                    'numero_pedido' => trim($cols[0]->nodeValue),
                    'codigo_produto' => '',
                    'descricao_produto' => trim($cols[5]->nodeValue),
                    'industria' => 'Cedasa',
                    'qtd_pallets' => (int) floatval(str_replace(',', '.', $cols[13]->nodeValue)),
                    'tipo_produto' => 'CERAMICA/PISO',
                    'total_m2' => floatval(str_replace(',', '.', $cols[8]->nodeValue)),
                    'peso_total' => floatval(str_replace(',', '.', $cols[12]->nodeValue)),
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
