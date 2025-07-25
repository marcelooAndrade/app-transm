<?php

namespace App\Scrapers;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\Carbon;

class VillagresScraper
{
    public static function coletar(): array
    {
        $client = new Client([
            'base_uri' => 'https://api-portal.villagres.com.br',
            'headers' => [
                'User-Agent' => 'Laravel-Scraper/1.0',
                'Accept'     => 'application/json',
            ],
        ]);

        // 1. Login e obtenção do token
        $loginRes = $client->post('/api/v1/session', [
            'json' => [
                'weu_email' => 'logistica@modestoemussato.com.br',
                'password'  => 'Marina@110967',
                'weu_id'    => 327,
            ],
        ]);

        if ($loginRes->getStatusCode() !== 200) {
            throw new \Exception("❌ Falha no login Villagres: {$loginRes->getStatusCode()}");
        }

        $dados = json_decode($loginRes->getBody(), true);
        $token = $dados['auth_token'] ?? null;

        if (!$token) {
            throw new \Exception("❌ Token não encontrado Villagres");
        }

        // 2. Requisição de pedidos com token
        $res = $client->get('/api/v1/orders_for_collect', [
            'headers' => [
                'Authorization' => "Bearer $token",
            ],
        ]);

        if ($res->getStatusCode() !== 200) {
            throw new \Exception("❌ Erro ao buscar pedidos Villagres: {$res->getStatusCode()}");
        }

        $ordersData = json_decode($res->getBody(), true)['orders'] ?? [];

        $pedidos = [];
        foreach ($ordersData as $order) {
            $cidadeEstado = explode('/', $order['cli_cidade'] ?? '');
            $cidade = $cidadeEstado[0] ?? 'ND';
            $estado = $cidadeEstado[1] ?? 'ND';

            foreach ($order['itens'] as $item) {
                $dataPedido = null;
                if (!empty($item['itped_data_entrega'])) {
                    try {
                        $dataPedido = Carbon::parse($item['itped_data_entrega'])->toDateString();
                    } catch (\Throwable $e) {
                        $dataPedido = null;
                    }
                }

                $pedidos[] = [
                    'representante'   => $item['rep_nome'] ?? '',
                    'data_pedido'     => $dataPedido,
                    'cliente'         => $order['cli_nome'] ?? '',
                    'numero_pedido'   => $item['cod_pedido'] ?? '',
                    'codigo_produto'  => $item['cod_produto'] ?? '',
                    'descricao_produto' => $item['dsc_abreviado'] ?? '',
                    'industria'       => 'Villagres Cerâmica',
                    'qtd_pallets'     => (int) round((float) ($item['qtd_pallet'] ?? 0)),
                    'tipo_produto'    => 'CERAMICA/PISO',
                    'total_m2'        => (float) ($item['qtd_saldo'] ?? 0),
                    'peso_total'      => (float) ($item['peso_bru'] ?? 0),
                    'cidade'          => $cidade,
                    'estado'          => $estado,
                    'status'          => 'pendente',
                ];
            }
        }

        return $pedidos;
    }
}
