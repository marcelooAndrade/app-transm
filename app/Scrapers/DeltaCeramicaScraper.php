<?php

namespace App\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeltaCeramicaScraper
{
    public static function coletar(): array
    {
        $urlLogin = 'https://portal-api.deltaceramica.com.br/api/v1/session';
        $urlPedidos = 'https://portal-api.deltaceramica.com.br/api/v1/orders_for_collect';

        $payload = [
            'weu_email' => 'logistica@modestoemussato.com.br',
            'password' => 'Marinapisos',
            'weu_id' => 910,
        ];

        $resLogin = Http::post($urlLogin, $payload);

        if (!$resLogin->ok()) {
            throw new \Exception("Falha no login Delta: " . $resLogin->status());
        }

        $token = $resLogin->json('auth_token');

        if (!$token) {
            throw new \Exception("Token de autenticação não encontrado.");
        }

        $resPedidos = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json'
        ])->get($urlPedidos);

        if (!$resPedidos->ok()) {
            throw new \Exception("Erro ao buscar pedidos: " . $resPedidos->status());
        }

        $dados = $resPedidos->json('orders') ?? [];
        $pedidos = [];

        foreach ($dados as $pedidoData) {
            foreach ($pedidoData['itens'] ?? [] as $item) {
                $cidadeEstado = explode('/', $pedidoData['cli_cidade'] ?? 'ND/ND');

                $pedidos[] = [
                    'representante' => $item['rep_nome'] ?? '',
                    'data_pedido' => substr($item['itped_data_entrega'] ?? '', 0, 10),
                    'cliente' => $pedidoData['cli_nome'] ?? '',
                    'numero_pedido' => $item['cod_pedido'] ?? '',
                    'codigo_produto' => $item['cod_produto'] ?? '',
                    'descricao_produto' => $item['dsc_abreviado'] ?? '',
                    'industria' => 'Delta Cerâmica',
                    'qtd_pallets' => (int) floatval($item['qtd_pallet'] ?? 0),
                    'tipo_produto' => 'CERAMICA/PISO',
                    'total_m2' => (float) $item['qtd_saldo'] ?? 0,
                    'peso_total' => (float) $item['peso_bru'] ?? 0,
                    'cidade' => trim($cidadeEstado[0] ?? 'ND'),
                    'estado' => trim($cidadeEstado[1] ?? 'ND'),
                    'status' => 'pendente'
                ];
            }
        }

        return $pedidos;
    }
}
