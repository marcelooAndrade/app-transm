<?php

namespace App\Scrapers;

use DOMDocument;
use DOMXPath;

class CeramicaCecafiScraper
{
    protected $baseUrl = 'https://transportadora.ccfonline.com.br';
    protected $loginUrl = '/auth/login';
    protected $agendamentoUrl = '/agendamento';
    protected $username = '47.528.089/0001-27';
    protected $password = '47528089';
    protected $cookieFile;

    public function __construct()
    {
        $this->cookieFile = storage_path('app/cookies_cecafi.txt');
    }

    public function coletar(): array
    {
        $this->login();
        $html = $this->getAgendamentoHtml();
        return $this->extrairPedidos($html);
    }


    protected function login()
    {
        $postFields = [
            '_token' => '8J18I6WeSbUV9VpTJAJtYJ3yWqf7Q38GVbjxcfZu',
            'cnpj' => $this->username,
            'password' => $this->password,
        ];

        // Fazer o login
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
        curl_exec($ch);
        curl_close($ch);

        // Acessar a home após login
        $ch = curl_init($this->baseUrl . '/');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $home = curl_exec($ch);
        curl_close($ch);

        if (
            strpos($home, 'P&aacute;gina inicial') === false ||
            strpos($home, 'Selecione uma das opções no menu acima') === false
        ) {
            throw new \Exception('Login falhou: página inicial não foi carregada corretamente.');
        }

        // Tudo certo: login bem-sucedido
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
    $dom = new \DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new \DOMXPath($dom);
    $pedidos = [];

    // Localiza todos os painéis com cliente
    $painelClientes = $xpath->query("//div[@id='contentAgendamento']//div[contains(@class, 'panel-info')]");

    foreach ($painelClientes as $painel) {
        $cliente = '';
        $cidade = '';
        $uf = '';

        $tabelaCliente = $xpath->query(".//table[contains(@class, 'table-condensed')]//tr", $painel);
        if ($tabelaCliente->length >= 2) {
            // Nome do cliente
            $linha1 = $tabelaCliente->item(0);
            $ths1 = $linha1->getElementsByTagName('th');
            if ($ths1->length > 0) {
                preg_match('/Cliente:\s*(.+)/', trim($ths1->item(0)->nodeValue), $m);
                $cliente = $m[1] ?? '';
            }

            // Cidade e UF
            $linha2 = $tabelaCliente->item(1);
            $tds2 = $linha2->getElementsByTagName('td');
            foreach ($tds2 as $td) {
                $txt = trim($td->nodeValue);
                if (str_contains($txt, 'Cidade:')) {
                    $cidade = trim(str_replace('Cidade:', '', $txt));
                }
                if (str_contains($txt, 'UF:')) {
                    $uf = trim(str_replace('UF:', '', $txt));
                }
            }
        }

        // Pega tabela de produtos após esse painel
        $produtosTabela = $painel->nextSibling;
        while ($produtosTabela && $produtosTabela->nodeType !== XML_ELEMENT_NODE) {
            $produtosTabela = $produtosTabela->nextSibling;
        }

        if ($produtosTabela) {
            $linhas = $xpath->query(".//tr[contains(@class, 'hoverLine')]", $produtosTabela);
            foreach ($linhas as $tr) {
                $pedido = [
                    'numero_pedido'     => $this->getNodeValue($xpath, './/td[contains(@id,"num")]', $tr),
                    'codigo_produto'    => $this->getNodeValue($xpath, './/td[contains(@id,"cod")]', $tr),
                    'descricao_produto' => $this->getNodeValue($xpath, './/td[contains(@id,"nome")]', $tr),
                    'data_pedido'       => date('Y-m-d'),
                    'qtd_pallets'       => (int) $this->getNodeValue($xpath, './/td[contains(@id,"pallets")]', $tr),
                    'total_m2'          => (float) $this->getNodeValue($xpath, './/td[contains(@id,"m2")]', $tr),
                    'peso_total'        => (float) $this->getNodeValue($xpath, './/td[contains(@id,"peso")]', $tr),
                    'cliente'           => $cliente,
                    'representante'     => $this->getNodeValue($xpath, './/td[contains(@id,"rep")]', $tr),
                    'estado'            => $uf,
                    'industria'         => 'Cecafi',
                    'tipo_produto'      => 'CERAMICA/PISO',
                    'cidade'            => $cidade,
                    'status'            => 'Pendente',
                ];

                if ($pedido['numero_pedido'] && $pedido['codigo_produto']) {
                    $pedidos[] = $pedido;
                }
            }
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
