<?php

namespace App\Services\BB;

use Illuminate\Support\Facades\Http;

class Pix
{
    private string $URL = 'https://api.hm.bb.com.br/pix/v2';

    private string $CHAVE;

    private string $GW_DEV_APP_KEY;

    private Autenticacao $autenticacao;

    public function __construct()
    {
        $this->autenticacao = new Autenticacao();
        $this->CHAVE = config('bancobrasil.chave_pix');
        $this->GW_DEV_APP_KEY = config('bancobrasil.gw_dev_app_key');
    }

    public function criarCobrancaImediata(array $devedor, string $valor)
    {
        $path = '/cob';
        $response = Http::withToken($this->autenticacao->getToken())
            ->withQueryParameters([
                'gw-dev-app-key' => $this->GW_DEV_APP_KEY,
            ])
            ->post($this->URL.$path, [
                'calendario' => [
                    'expiracao' => 3600,
                ],
                'valor' => [
                    'original' => $valor
                ],
                'chave' => $this->CHAVE,
                'devedor' => $devedor,
            ]);
        if ($response->successful()) {
            return $response;
        }
        $response->throw();
    }

    public function consultarCobrancaImediata($txid)
    {
        $path = '/cob'.'/'.$txid;
        $response = Http::withToken($this->autenticacao->getToken())
            ->withQueryParameters([
                'gw-dev-app-key' => $this->GW_DEV_APP_KEY,
            ])
            ->get($this->URL.$path);
        if ($response->successful()) {
            return $response;
        }
        $response->throw();
    }
}
