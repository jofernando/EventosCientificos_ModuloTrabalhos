<?php

namespace App\Http\Controllers\Inscricao\Pagamento;

use App\Models\Inscricao\Inscricao;
use App\Models\Inscricao\Pagamento;
use App\Models\Inscricao\TipoPagamento;
use App\Models\Submissao\Evento;
use Illuminate\Http\Request;
use MercadoPago\Payer;
use MercadoPago\Payment;
use MercadoPago\SDK;

class MercadoPago implements PagamentoInterface
{
    private function cartao($payment, $contents)
    {
        $payment->token = $contents['token'];
        $payment->installments = $contents['installments'];
        $payment->payment_method_id = $contents['payment_method_id'];
        $payment->issuer_id = $contents['issuer_id'];
        $payer = new Payer();
        $payer->email = $contents['payer']['email'];
        $payer->identification = array(
            "type" => $contents['payer']['identification']['type'],
            "number" => $contents['payer']['identification']['number'],
        );
        $payment->payer = $payer;
    }

    private function pix($payment, $contents, $user)
    {
        $payment->payment_method_id = "pix";
        $payment->payer = array(
            "email" => $contents['payer']['email'],
            "first_name" => $user->name,
            "last_name" => "User",
            "identification" => array(
                "type" => "CPF",
                "number" => $user->cpf,
            ),
            "address" =>  array(
                "zip_code" => $user->endereco->cep,
                "street_name" => $user->endereco->rua,
                "street_number" => $user->endereco->numero,
                "neighborhood" => $user->endereco->bairro,
                "city" => $user->endereco->cidado,
                "federal_unit" => $user->endereco->uf,
            ),
        );
    }

    private function boleto($payment, $contents)
    {
        $payment->payment_method_id = $contents['payment_method_id'];
        $payment->payer = array(
            "email" =>  $contents['payer']['email'],
            "first_name" => $contents['payer']['first_name'],
            "last_name" => $contents['payer']['last_name'],
            "identification" => array(
                "type" => $contents['payer']['identification']['type'],
                "number" => $contents['payer']['identification']['number'],
            ),
            "address"=>  array(
                "zip_code" => $contents['payer']['address']['zip_code'],
                "street_name" => $contents['payer']['address']['street_name'],
                "street_number" => $contents['payer']['address']['street_number'],
                "neighborhood" => $contents['payer']['address']['neighborhood'],
                "city" => $contents['payer']['address']['city'],
                "federal_unit" => $contents['payer']['address']['federal_unit'],
            ),
        );
    }

    public function telaPagamento(Evento $evento)
    {
        $key = config('mercadopago.public_key');
        $user = auth()->user();
        $inscricao = $evento->inscricaos()->where('user_id', $user->id)->first();
        $categoria = $inscricao?->categoria;
        if ($inscricao->pagamento != null) {
            return redirect()->route('checkout.statusPagamento', ['evento' => $evento->id]);
        }

        return view('inscricao.pagamento.brick', compact('evento', 'inscricao', 'user', 'categoria', 'key'));
    }

    public function telaStatus(Evento $evento)
    {
        $key = config('mercadopago.public_key');
        $user = auth()->user();
        $inscricao = $evento->inscricaos()->where('user_id', $user->id)->first();
        $pagamento = $inscricao?->pagamento;
        if ($pagamento == null) {
            return redirect()->route('evento.visualizar', ['id' => $evento->id])->with('message', 'Não existe um pagamento para esse evento.');
        }

        return view('inscricao.pagamento.status', compact('pagamento', 'key'));
    }

    public function webhook(Request $request)
    {
        SDK::setAccessToken(config('mercadopago.access_token'));
        $contents = $request->all();
        switch($contents["type"]) {
            case "payment":
                $payment = Payment::find_by_id($contents["data"]["id"]);
                $pagamento = Pagamento::where('codigo', $contents["data"]["id"])->first();
                if ($payment->status == 'approved') {
                    $inscricao = $pagamento->inscricao;
                    $inscricao->finalizada = true;
                    $inscricao->save();
                }
                $pagamento->status = $payment->status;
                $pagamento->save();
                break;
            case "plan":
            case "subscription":
            case "invoice":
            case "point_integration_wh":
                break;
        }
        return response(status: 200);
    }

    public function processarPagamento(Request $request)
    {
        SDK::setAccessToken(config('mercadopago.access_token'));
        $contents = $request->all();
        $evento = Evento::find($contents['evento']);
        $user = auth()->user();
        $inscricao = $evento->inscricaos()->where('user_id', $user->id)->first();
        $categoria = $inscricao->categoria;
        $descricao = 'Inscrição no evento '.$evento->nome.' com valor de '.$categoria->valor_total;
        $payment = new Payment();
        $payment->transaction_amount = $categoria->valor_total;
        $payment->description = $descricao;

        switch ($request['payment_method_id']) {
            case 'pix':
                $this->pix($payment, $contents, $user);
                $tipo_pagamento = TipoPagamento::where('descricao', 'pix')->first();
                break;
            case 'bolbradesco':
            case 'pec':
                $this->boleto($payment, $contents);
                $tipo_pagamento = TipoPagamento::where('descricao', 'boleto')->first();
                break;
            default:
                $this->cartao($payment, $contents);
                $tipo_pagamento = TipoPagamento::where('descricao', 'cartao')->first();
                break;
        }

        // $payment->notification_url = route('checkout.notifications');
        $response = $payment->save();
        if (! $response || $payment->id == null) {
            throw new Exception($payment->error->message);
        }
        $pagamento = Pagamento::create([
            'valor' => $categoria->valor_total,
            'tipo_pagamento_id' => $tipo_pagamento->id,
            'descricao' => $descricao,
            'codigo' => $payment->id,
            'status' => $payment->status,
        ]);

        $inscricao->pagamento_id = $pagamento->id;
        $inscricao->save();
        $data = [
            'redirect_url' => route('checkout.statusPagamento', $evento),
        ];
        return response()->json($data);
    }
}
