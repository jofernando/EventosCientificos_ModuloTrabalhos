<?php

namespace App\Http\Controllers\Inscricao;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Inscricao\Pagamento\BancoBrasil;
use App\Http\Controllers\Inscricao\Pagamento\MercadoPago;
use App\Http\Controllers\Inscricao\Pagamento\PagamentoInterface;
use App\Models\Submissao\Evento;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    private PagamentoInterface $psp;

    public function __construct()
    {
        $metodo = config('app.metodo_pagamento');
        switch ($metodo) {
            case 'mercadopago':
                $this->psp = new MercadoPago();
                break;
            case 'bancobrasil':
                $this->psp = new BancoBrasil();
        }
    }

    public function telaPagamento(Evento $evento)
    {
        return $this->psp->telaPagamento($evento);
    }

    public function statusPagamento(Evento $evento)
    {
        return $this->psp->telaStatus($evento);
    }

    public function listarPagamentos($id)
    {
        $evento = Evento::find($id);

        $inscricaos = $evento->inscricaos;

        return view('coordenador.inscricoes.pagamentos', compact('evento', 'inscricaos'));
    }

    public function processPayment(Request $request)
    {
        return $this->psp->processarPagamento($request);
    }

    public function notifications(Request $request)
    {
        return $this->psp->webhook($request);
    }

}
