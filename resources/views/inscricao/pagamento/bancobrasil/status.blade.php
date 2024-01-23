@extends('layouts.app')

@section('content')
    <div class="container-sm">
        <h2 class="text-center">Pagamento</h2>
        <div class="card border rounded-top">
            <div class="card-header py-4">
                <h3 class="text-center my-3">Seu pagamento está {{ $response['status'] }}</h3>
            </div>
            <div id="pixcopiaecola" class="row justify-content-center py-2">
                {{ QrCode::size(200)->generate($response['pixCopiaECola']) }}
            </div>
            <div class="card-body">
                <p class="card-text">Realize o pagamento escaneando o QrCode pelo aplicativo do seu banco ou copiando o código Pix Copia e Cola: {{ $response['pixCopiaECola'] }}</p>
            </div>
        </div>
    </div>
@endsection
