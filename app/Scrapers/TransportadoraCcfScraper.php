<?php

namespace App\Scrapers;

use DOMDocument;
use DOMXPath;

class TransportadoraCcfScraper
{
    protected $baseUrl = 'https://transportadora.ccfonline.com.br';
    protected $loginUrl = '/auth/login';
    protected $agendamentoUrl = '/agendamento';
    protected $cnpj = '47.528.089/0001-27';
    protected $password = '47528089';
    protected $cookieFile;

    public function __construct()
    {
        $this->cookieFile = storage_path('app/cookies_ccf.txt');
    }

    public function coletar(): array
    {
        $this->login();
        $html = $this->getAgendamentoHtml();
        return $this->extrairPedidos($html);
    }

    protected function login()
    {
        $ch = curl_init($this->baseUrl . $this->loginUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $loginPage = curl_exec($ch);
        curl_close($ch);

        preg_match('/name="_token" value="([^"]+)"/', $loginPage, $matches);
        if (!isset($matches[1])) {
            throw new \Exception('Token CSRF não encontrado');
        }
        $token = $matches[1];

        $postFields = http_build_query([
            '_token' => $token,
            'cnpj' => $this->cnpj,
            'password' => $this->password,
        ]);

        $ch = curl_init($this->baseUrl . $this->loginUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (strpos($response, 'agendamento') === false) {
            throw new \Exception('Login falhou ou resposta inválida');
        }
    }

    protected function getAgendamentoHtml(): string
    {
        $ch = curl_init($this->baseUrl . $this->agendamentoUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $html = curl_exec($ch);
        curl_close($ch);

        return $html;
    }

    protected function extrairPedidos(string $html): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $linhas = $xpath->query("//tr[contains(@class, 'hoverLine')]");
        $pedidos = [];

        foreach ($linhas as $tr) {
            $pedido = [
                'numero_pedido'     => $this->getNodeValue($xpath, ".//td[contains(@id,'ordem') or contains(@id,'num') or contains(@id,'pedido')]", $tr),
                'codigo_produto'    => $this->getNodeValue($xpath, ".//td[contains(@id,'cod') or contains(@id,'codigo')]", $tr),
                'descricao_produto' => $this->getNodeValue($xpath, ".//td[contains(@id,'nome') or contains(@id,'produto')]", $tr),
                'data_pedido'       => date('Y-m-d'),
                'qtd_pallets'       => (int) $this->getNodeValue($xpath, ".//td[contains(@id,'pallets') or contains(@id,'pallet')]", $tr),
                'total_m2'          => (float) $this->getNodeValue($xpath, ".//td[contains(@id,'m2') or contains(@id,'metros')]", $tr),
                'peso_total'        => (float) $this->getNodeValue($xpath, ".//td[contains(@id,'peso') or contains(@id,'kg')]", $tr),
                'cliente'           => $this->getNodeValue($xpath, ".//td[contains(@id,'cliente') or contains(@id,'nomeCliente')]", $tr),
                'representante'     => $this->getNodeValue($xpath, ".//td[contains(@id,'rep') or contains(@id,'representante')]", $tr),
                'estado'            => $this->getNodeValue($xpath, ".//td[contains(@id,'codUF') or contains(@id,'estado')]", $tr),
                'industria'         => 'Cecafi',
                'tipo_produto'      => 'CERAMICA/PISO',
                'cidade'            => 'ND',
                'status'            => 'pendente',
            ];

            if ($pedido['numero_pedido'] && $pedido['codigo_produto']) {
                $pedidos[] = $pedido;
            }
        }

        return $pedidos;
    }

    protected function getNodeValue(DOMXPath $xpath, string $query, \DOMNode $context): string
    {
        $node = $xpath->query($query, $context);
        return $node->length ? trim($node->item(0)->nodeValue) : '';
    }
}
