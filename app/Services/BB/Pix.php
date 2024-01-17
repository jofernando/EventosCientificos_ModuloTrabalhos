<?php

namespace App\Services\BB;

use App\Models\Submissao\Evento;
use App\Models\Users\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Pix
{
    private string $URL = 'https://api.sandbox.bb.com.br/pix/v2';

    private string $CHAVE;

    private Autenticacao $autenticacao;

    public function __construct()
    {
        $this->autenticacao = new Autenticacao();
        $this->CHAVE = config('bancobrasil.chave_pix');
    }

    public function criarCobrancaImediata(User $user, Evento $evento, string $valor)
    {
        $path = '/cob';
        $response = Http::withToken($this->autenticacao->getToken())
            ->withOptions([
                'cert' => Storage::path('api.sandbox.bb.com.pem'),
            ])
            ->post($this->URL.$path, [
                'calendario' => [
                    'expiracao' => 3600,
                ],
                'valor' => [
                    'original' => $valor
                ],
                'chave' => $this->CHAVE,
                'devedor' => [
                    'logradouro' => $user->endereco->rua,
                    'cidade' => $user->endereco->cidade,
                    'uf' => $user->endereco->uf,
                    'cep' => $this->somenteDigitos($user->endereco->cep),
                    'email' => $user->email,
                    'nome' => $user->name,
                    $this->cpfCnpjLabel($user) => $this->cpfCnpj($user),
                ],
            ]);
        if ($response->successful()) {
            return $response['txid'];
        }
        $response->throw();
    }

    private function somenteDigitos($cep)
    {
        return preg_replace('/[^0-9]/', '', $cep);
    }

    private function cpfCnpj(User $user)
    {
        $cpfCnpj = $user->cpf != null ? $user->cpf : $user->cnpj;
        return $this->somenteDigitos($cpfCnpj);
    }

    private function cpfCnpjLabel(User $user)
    {
        return $user->cpf != null ? 'cpf' : 'cnpj';
    }
}
