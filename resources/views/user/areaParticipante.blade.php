@extends('layouts.app')

@section('content')

<div class="container"  >

    {{-- titulo da página --}}
    <div class="row justify-content-center titulo">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-10">
                    <h1>Eventos Atuais</h1>
                </div>
                <div class="col-sm-2">
                    <a href="{{route('user.meusTrabalhos')}}" class="btn btn-primary">Trabalhos</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">


        @foreach ($eventos as $evento)
            <div class="card" style="width: 18rem;">
                @if(isset($evento->fotoEvento))
                  <img src="{{asset('storage/eventos/'.$evento->id.'/logo.png')}}" class="card-img-top" alt="...">
                @else
                  <img src="{{asset('img/colorscheme.png')}}" class="card-img-top" alt="...">
                @endif
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <h4 class="card-title">
                                <div class="row justify-content-center">
                                    <div class="col-sm-12">
                                        {{$evento->nome}}
                                    </div>

                                </div>

                            </h4>

                        </div>
                    </div>
                    <p class="card-text">
                        <strong>Realização:</strong> {{date('d/m/Y',strtotime($evento->dataInicio))}} - {{date('d/m/Y',strtotime($evento->dataFim))}}<br>
                        <strong>Submissão:</strong> {{date('d/m/Y',strtotime($evento->inicioSubmissao))}} - {{date('d/m/Y',strtotime($evento->fimSubmissao))}}<br>
                        <strong>Revisão:</strong> {{date('d/m/Y',strtotime($evento->inicioRevisao))}} - {{date('d/m/Y',strtotime($evento->fimRevisao))}}<br>
                    </p>
                    <p>

                        <div class="row justify-content-center">
                            <div class="col-sm-12">
                                <img src="{{asset('img/icons/map-marker-alt-solid.svg')}}" alt="" style="width:15px">
                                {{$evento->endereco->rua}}, {{$evento->endereco->numero}} - {{$evento->endereco->cidade}} / {{$evento->endereco->uf}}.
                            </div>
                        </div>
                    </p>
                    <p>
                        <a href="{{  route('evento.visualizar',['id'=>$evento->id])  }}" class="visualizarEvento">Visualizar Evento</a>
                    </p>
                </div>

            </div>
        @endforeach
    </div>

</div>

@endsection
