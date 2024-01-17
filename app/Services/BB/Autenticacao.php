<?php

namespace App\Services\BB;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Autenticacao
{
    private string $URL = 'https://oauth.hm.bb.com.br/oauth/token';

    private array $BODY = [
        'grant_type' => 'client_credentials',
        'scope' => 'cob.write cob.read',
    ];

    private string $CLIENT_ID;
    private string $CLIENT_SECRET;

    public function __construct()
    {
        $this->CLIENT_ID = config('bancobrasil.client.id');
        $this->CLIENT_SECRET = config('bancobrasil.client.secret');
    }

    public function getToken()
    {
        $seconds = 590;
        return Cache::remember('bb_access_token', $seconds, function () {
            return $this->createToken();
        });
    }

    /**
     * @throws RequestException
     */
    private function createToken()
    {
        $response = Http::withBasicAuth($this->CLIENT_ID, $this->CLIENT_SECRET)
            ->asForm()
            ->post($this->URL, $this->BODY);
        if ($response->successful()) {
            return $response['access_token'];
        }
        $response->throw();
    }
}
