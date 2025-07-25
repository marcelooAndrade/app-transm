<?php

namespace App\Scrapers;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\PedidoLogistica;
use Carbon\Carbon;

class CeramicaAlmeidaScraper
{
    public static function coletar(): array
    {
        $client = new Client([
            'base_uri' => 'http://clientes.ceramicaalmeida.com.br:51110',
            'cookies' => true,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0',
            ],
        ]);

        // 1. Página inicial para pegar o token CSRF
        $resLogin = $client->get('/Login/Transportadora');
        $html = (string) $resLogin->getBody();
        $crawler = new Crawler($html);
        $token = $crawler->filter('input[name="__RequestVerificationToken"]')->attr('value');

        if (!$token) {
            throw new \Exception("❌ Token CSRF não encontrado.");
        }

        // 2. Envia o formulário de login
        $resAuth = $client->post('/Login/SenhaTransportadora', [
            'form_params' => [
                '__RequestVerificationToken' => $token,
                'Email' => 'faturamento@modestoemussato.com.br',
                'Senha' => 'modesto@123',
            ],
            'headers' => [
                'Referer' => 'http://clientes.ceramicaalmeida.com.br:51110/Login/Transportadora',
            ]
        ]);

        $html = (string) $resAuth->getBody();
        if (!str_contains($html, 'tabelaultimospedidos')) {
            throw new \Exception("❌ Falha ao autenticar no portal da Cerâmica Almeida.");
        }

        // 3. Busca pedidos
        $crawler = new Crawler($html);
        $linhas = $crawler->filter('#tabelaultimospedidos tbody tr');
        $pedidos = [];

        foreach ($linhas as $linha) {
            $cols = (new Crawler($linha))->filter('td');
            if ($cols->count() < 3) continue;

            $pesoTotal = floatval(str_replace(',', '.', $cols->eq(2)->text()));

            $pedidos[] = [
                'representante' => 'Não tem',
                'data_pedido' => null,
                'cliente' => $cols->eq(1)->text(),
                'numero_pedido' => $cols->eq(0)->text(),
                'codigo_produto' => 'Não tem',
                'descricao_produto' => 'Não tem',
                'industria' => 'Almeida',
                'qtd_pallets' => 0,
                'tipo_produto' => 'CERAMICA/PISO',
                'total_m2' => 0.0,
                'peso_total' => $pesoTotal,
                'cidade' => 'ND',
                'estado' => 'ND',
                'status' => 'Pendente',
            ];
        }

        return $pedidos;
    }
}
