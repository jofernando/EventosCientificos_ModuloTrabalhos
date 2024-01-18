<?php

namespace App\Http\Controllers\Inscricao\Pagamento;

use App\Models\Submissao\Evento;
use Illuminate\Http\Request;

interface PagamentoInterface
{
    public function telaPagamento(Evento $evento);

    public function processarPagamento(Request $request);
    public function telaStatus(Evento $evento);
    public function webhook(Request $request);
}
