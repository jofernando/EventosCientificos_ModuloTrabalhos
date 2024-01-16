<?php

namespace App\Http\Controllers\Inscricao;

use App\Http\Controllers\Controller;
use App\Models\Inscricao\Pagamento;
use App\Models\Inscricao\TipoPagamento;
use App\Models\Submissao\Evento;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{

    public function telaPagamento(Evento $evento)
    {
        $key = env('MERCADOPAGO_PUBLIC_KEY');
        $user = auth()->user();
        $inscricao = $evento->inscricaos()->where('user_id', $user->id)->first();
        $categoria = $inscricao?->categoria;
        if ($inscricao->pagamento != null) {
            return redirect()->route('checkout.statusPagamento', ['evento' => $evento->id]);
        }

        return view('inscricao.pagamento.brick', compact('evento', 'inscricao', 'user', 'categoria', 'key'));
    }

    public function statusPagamento(Evento $evento)
    {
        $key = env('MERCADOPAGO_PUBLIC_KEY');
        $user = auth()->user();
        $inscricao = $evento->inscricaos()->where('user_id', $user->id)->first();
        $pagamento = $inscricao?->pagamento;
        if ($pagamento == null) {
            return redirect()->route('evento.visualizar', ['id' => $evento->id])->with('message', 'Não existe um pagamento para esse evento.');
        }

        return view('inscricao.pagamento.status', compact('pagamento', 'key'));
    }

    public function listarPagamentos($id)
    {
        $evento = Evento::find($id);

        $inscricaos = $evento->inscricaos;

        return view('coordenador.programacao.pagamentos', compact('evento', 'inscricaos'));
    }

    private function cartao(Request $request)
    {
        \MercadoPago\SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));
        $contents = $request->all();
        $evento = Evento::find($contents['evento']);
        $user = auth()->user();
        $inscricao = $evento->inscricaos()->where('user_id', $user->id)->first();
        $categoria = $inscricao->categoria;

        $payment = new \MercadoPago\Payment();
        $payment->transaction_amount = $categoria->valor_total;
        $payment->token = $contents['token'];
        $payment->installments = $contents['installments'];
        $payment->payment_method_id = $contents['payment_method_id'];
        $payment->issuer_id = $contents['issuer_id'];
        $payer = new \MercadoPago\Payer();
        $payer->email = $contents['payer']['email'];
        $payer->identification = array(
            "type" => $contents['payer']['identification']['type'],
            "number" => $contents['payer']['identification']['number'],
        );
        $payment->payer = $payer;
        $payment->notification_url = route('checkout.notifications');
        $payment->save();
        $response = array(
            'status' => $payment->status,
            'status_detail' => $payment->status_detail,
            'id' => $payment->id,
        );
        $tipo_pagamento = TipoPagamento::where('descricao', 'cartao')->first();
        $descricao = 'Inscrição no evento '.$evento->nome.' com valor de '.$categoria->valor_total;
        $pagamento = Pagamento::create([
            'valor' => $categoria->valor_total,
            'tipo_pagamento_id' => $tipo_pagamento->id,
            'descricao' => $descricao,
            'codigo' => $payment->id,
            'status' => $payment->status,
        ]);
        $inscricao->pagamento_id = $pagamento->id;
        $inscricao->save();
        return redirect()->route('checkout.statusPagamento', ['evento' => $evento->id]);
    }

    private function pix(Request $request)
    {
        \MercadoPago\SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));
        $evento = Evento::find($request->evento);
        $user = auth()->user();
        $inscricao = $evento->inscricaos()->where('user_id', $user->id)->first();
        $categoria = $inscricao->categoria;
        $descricao = 'Inscrição no evento '.$evento->nome.' com valor de '.$categoria->valor_total;
        $contents = $request->all();

        $payment = new \MercadoPago\Payment();
        $payment->transaction_amount = $categoria->valor_total;
        $payment->description = $descricao;
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

        $payment->notification_url = route('checkout.notifications');
        $payment->save();
        $response = array(
            'status' => $payment->status,
            'status_detail' => $payment->status_detail,
            'id' => $payment->id,
        );
        $tipo_pagamento = TipoPagamento::where('descricao', 'pix')->first();
        $descricao = 'Inscrição no evento '.$evento->nome.' com valor de '.$categoria->valor_total;
        $pagamento = Pagamento::create([
            'valor' => $categoria->valor_total,
            'tipo_pagamento_id' => $tipo_pagamento->id,
            'descricao' => $descricao,
            'codigo' => $payment->id,
            'status' => $payment->status,
        ]);
        $inscricao->pagamento_id = $pagamento->id;
        $inscricao->save();
        return redirect()->route('checkout.statusPagamento', ['evento' => $evento->id]);
    }

    private function boleto(Request $request)
    {
        \MercadoPago\SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));
        $contents = $request->all();
        $evento = Evento::find($contents['evento']);
        $user = auth()->user();
        $inscricao = $evento->inscricaos()->where('user_id', $user->id)->first();
        $categoria = $inscricao->categoria;

        $payment = new \MercadoPago\Payment();
        $payment->transaction_amount = $contents['transaction_amount'];
        $descricao = 'Inscrição no evento '.$evento->nome.' com valor de '.$categoria->valor_total;
        $payment->description = $descricao;
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
        $payment->notification_url = route('checkout.notifications');
        $payment->save();
        $response = array(
            'status' => $payment->status,
            'status_detail' => $payment->status_detail,
            'id' => $payment->id,
        );
        $tipo_pagamento = TipoPagamento::where('descricao', 'boleto')->first();
        $descricao = 'Inscrição no evento '.$evento->nome.' com valor de '.$categoria->valor_total;
        $pagamento = Pagamento::create([
            'valor' => $categoria->valor_total,
            'tipo_pagamento_id' => $tipo_pagamento->id,
            'descricao' => $descricao,
            'codigo' => $payment->id,
            'status' => $payment->status,
        ]);
        $inscricao->pagamento_id = $pagamento->id;
        $inscricao->save();
        return redirect()->route('checkout.statusPagamento', ['evento' => $evento->id]);
    }

    public function processPayment(Request $request)
    {
        switch ($request['payment_method_id']) {
            case 'pix':
                return $this->pix($request);
            case 'bolbradesco':
            case 'pec':
                return $this->boleto($request);
            default:
                return $this->cartao($request);
        }
    }

    public function notifications(Request $request)
    {
        \MercadoPago\SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));
        $contents = $request->all();
        switch($contents["type"]) {
            case "payment":
                $payment = \MercadoPago\Payment::find_by_id($contents["data"]["id"]);
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

}
