@extends('layouts.app')

@section('content')

<div class="container"  >

    {{-- titulo da página --}}
    <div class="row justify-content-center titulo">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-10">
                    <h1>Editar Trabalho</h1>
                </div>

            </div>
        </div>

    </div>
    @if(session('mensagem'))
        <div class="row">
            <div class="col-md-12" style="margin-top: 5px;">
                <div class="alert alert-success">
                    <p>{{session('mensagem')}}</p>
                </div>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="modal-body">
            <form id="formEditarTrab{{$trabalho->id}}" action="{{route('editar.trabalho', ['id' => $trabalho->id])}}" method="POST" enctype="multipart/form-data">
              @csrf

              @php
                $formSubTraba = $trabalho->evento->formSubTrab;
                $ordem = explode(",", $formSubTraba->ordemCampos);
                $modalidade = $trabalho->modalidade;
                $areas = $trabalho->evento->areas;
              @endphp
              <input type="hidden" name="trabalhoEditId" value="{{$trabalho->id}}">
              @error('numeroMax'.$trabalho->id)
                <div class="row">
                  <div class="col-md-12">
                    <div class="alert alert-danger" role="alert">
                      {{ $message }}
                    </div>
                  </div>
                </div>
              @enderror
            @if($errors->any())
                <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <p>{{$error}}</p>
                @endforeach
                </div>
            @endif
              @foreach ($ordem as $indice)
                @if ($indice == "etiquetatitulotrabalho")
                  <div class="row justify-content-center">
                    {{-- Nome Trabalho  --}}
                    <div class="col-sm-12">
                        <label for="nomeTrabalho_{{$trabalho->id}}" class="col-form-label">{{ $formSubTraba->etiquetatitulotrabalho }}</label>
                        <input id="nomeTrabalho_{{$trabalho->id}}" type="text" class="form-control @error('nomeTrabalho'.$trabalho->id) is-invalid @enderror" name="nomeTrabalho{{$trabalho->id}}" value="{{old('nomeTrabalho'.$trabalho->id, $trabalho->titulo)}}" autocomplete="nomeTrabalho" autofocus>

                        @error('nomeTrabalho'.$trabalho->id)
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                  </div>
                @endif
                @if ($indice == "etiquetacoautortrabalho")
                  <div class="flexContainer" style="margin-top:20px" x-data="handler()">
                    <div class="row">
                        <div class="col">
                            <h4>{{$evento->formSubTrab->etiquetaautortrabalho}}</h4>
                        </div>
                        <div class="col mr-5">
                            <div class="float-right">
                                <button type="button" class="btn btn-link" title="Clique aqui para adicionar {{$evento->formSubTrab->etiquetacoautortrabalho}}, se houver"  @click="adicionaAutor">
                                    <i class="fas fa-user-plus fa-2x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                        <div class="flexContainer">
                            <template x-for="(autor, index) in autores" :key="index">
                                <div class="row">
                                    <template x-if="index == 1">
                                        <h4 class="col-sm-12" id="title-coautores"
                                            style="margin-top:20px">
                                            {{$evento->formSubTrab->etiquetacoautortrabalho}}
                                        </h4>
                                    </template>
                                    <div class="item card w-100">
                                        <div class="row card-body">
                                            <div :class="index == 0 ? 'col-md-6' : 'col-md-4 col-lg-4'">
                                                <label :for="'email' + index">E-mail</label>
                                                <input type="email" style="margin-bottom:10px"
                                                    class="form-control emailCoautor"
                                                    name="emailCoautor_{{$trabalho->id}}[]" placeholder="E-mail"
                                                    :id="'email' + index"
                                                    x-init="$nextTick(() => centralizarTela(index))"
                                                    x-on:focusout="checarNome(index)"
                                                    x-model="autor.email">
                                            </div>
                                            <div :class="index == 0 ? 'col-md-6' : 'col-md-4 col-lg-5'">
                                                <label :for="'nome' + index">Nome Completo</label>
                                                <input type="text" style="margin-bottom:10px"
                                                    class="form-control emailCoautor"
                                                    name="nomeCoautor_{{$trabalho->id}}[]" placeholder="Nome"
                                                    :id="'nome' + index"
                                                    x-model="autor.nome">
                                            </div>
                                            <template x-if="index > 0">
                                                <div class="col-md-4 col-lg-3 justify-content-center d-flex align-items-end btn-group pb-1">
                                                    <button type="button" @click="removeAutor(index)" style="color: #d30909;" class="btn"><i class="fas fa-user-times fa-2x"></i></button>
                                                    <button type="button" @click="sobeAutor(index)" class="btn btn-link"><i class="fas fa-arrow-up fa-2x"></i></button>
                                                    <button type="button" @click="desceAutor(index)" class="btn btn-link"><i class="fas fa-arrow-down fa-2x"></i></button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                @endif
                @if ($modalidade->texto && $indice == "etiquetaresumotrabalho")
                  @if ($modalidade->caracteres == true)
                    <div class="row justify-content-center">
                      <div class="col-sm-12">
                          <label for="resumo_{{$trabalho->id}}" class="col-form-label">{{$formSubTraba->etiquetaresumotrabalho}}</label>
                          <textarea id="resumo_{{$trabalho->id}}" class="char-count form-control @error('resumo'.$trabalho->id) is-invalid @enderror" data-ls-module="charCounter" minlength="{{$modalidade->mincaracteres}}" maxlength="{{$modalidade->maxcaracteres}}" name="resumo{{$trabalho->id}}"  autocomplete="resumo" autofocusrows="5">{{old('resumo'.$trabalho->id, $trabalho->resumo)}}</textarea>
                          <p class="text-muted"><small><span id="resumo{{$trabalho->id}}">{{strlen($trabalho->resumo)}}</span></small> - Min Caracteres: {{$modalidade->mincaracteres}} - Max Caracteres: {{$modalidade->maxcaracteres}}</p>
                          @error('resumo'.$trabalho->id)
                          <span class="invalid-feedback" role="alert">
                              <strong>{{ $message }}</strong>
                          </span>
                          @enderror

                      </div>
                    </div>
                  @elseif ($modalidade->palavras == true)
                    <div class="row justify-content-center">
                      <div class="col-sm-12">
                          <label for="resumo_{{$trabalho->id}}" class="col-form-label">{{$formSubTraba->etiquetaresumotrabalho}}</label>
                          <textarea id="resumo_{{$trabalho->id}}" class="form-control palavra @error('resumo'.$trabalho->id) is-invalid @enderror" name="resumo{{$trabalho->id}}" required autocomplete="resumo" autofocusrows="5">{{old('resumo'.$trabalho->id, $trabalho->resumo)}}</textarea>
                          <p class="text-muted"><small><span id="resumo{{$trabalho->id}}">{{count(explode(" ", $trabalho->resumo))}}</span></small> - Min Palavras: <span id="minpalavras">{{$modalidade->minpalavras}}</span> - Max Palavras: <span id="maxpalavras">{{$modalidade->maxpalavras}}</span></p>
                          @error('resumo'.$trabalho->id)
                          <span class="invalid-feedback" role="alert">
                              <strong>{{ $message }}</strong>
                          </span>
                          @enderror

                      </div>
                    </div>
                  @endif
                @endif
                @if ($indice == "etiquetaareatrabalho")
                  <!-- Areas -->
                  <div class="row justify-content-center">
                      <div class="col-sm-12">
                          <label for="area_{{$trabalho->id}}" class="col-form-label">{{$formSubTraba->etiquetaareatrabalho}}</label>
                          <select id="area_{{$trabalho->id}}" class="form-control @error('area'.$trabalho->id) is-invalid @enderror" name="area{{$trabalho->id}}" required>
                              <option value="" disabled selected hidden>-- Área --</option>
                              {{-- Apenas um teste abaixo --}}
                              @if (old('area'.$trabalho->id) != null)
                                @foreach($areas as $area)
                                  <option value="{{$area->id}}" @if(old('area'.$trabalho->id) == $area->id) selected @endif>{{$area->nome}}</option>
                                @endforeach
                              @else
                                @foreach($areas as $area)
                                  <option value="{{$area->id}}" @if($trabalho->areaId == $area->id) selected @endif>{{$area->nome}}</option>
                                @endforeach
                              @endif

                          </select>
                          @error('area'.$trabalho->id)
                          <span class="invalid-feedback" role="alert" style="overflow: visible; display:block">
                            <strong>{{ $message }}</strong>
                          </span>
                          @enderror
                      </div>
                  </div>
                  <!-- Modalidades -->
                <div class="row justify-content-center">
                    <div class="col-sm-12">
                        <label for="modalidade_{{$trabalho->id}}" class="col-form-label">Modalidade</label>
                        <select id="modalidade_{{$trabalho->id}}" class="form-control @error('modalidadeError'.$trabalho->id) is-invalid @enderror" name="modalidade{{$trabalho->id}}" required>
                            <option value="" disabled selected hidden>-- Modalidade --</option>
                            @if (old('modalidade'.$trabalho->id) != null)
                              @foreach($modalidades as $modalidade)
                                <option value="{{$modalidade->id}}" @if(old('modalidade'.$trabalho->id) == $modalidade->id) selected @endif>{{$modalidade->nome}}</option>
                              @endforeach
                            @else
                              @foreach($modalidades as $modalidade)
                                <option value="{{$modalidade->id}}" @if($trabalho->modalidadeId == $modalidade->id) selected @endif>{{$modalidade->nome}}</option>
                              @endforeach
                            @endif

                        </select>
                        @error('modalidadeError'.$trabalho->id)
                            <span class="invalid-feedback" role="alert" style="overflow: visible; display:block">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                @endif
                @if ($indice == "etiquetauploadtrabalho")
                  <div class="row justify-content-center">
                    {{-- Submeter trabalho --}}

                    @if ($modalidade->arquivo == true)
                      <div class="col-sm-12" style="margin-top: 20px;">
                        <label for="nomeTrabalho" class="col-form-label">{{$formSubTraba->etiquetauploadtrabalho}}:</label>
                          <a href="{{route('downloadTrabalho', ['id' => $trabalho->id])}}">Arquivo atual</a>
                        <br>
                        <small>Para trocar o arquivo envie um novo.</small>
                        <div class="custom-file">
                          <input type="file" class="filestyle" data-placeholder="Nenhum arquivo" data-text="Selecionar" data-btnClass="btn-primary-lmts" name="arquivo{{$trabalho->id}}">
                        </div>
                        <small>Arquivos aceitos nos formatos
                          @if($modalidade->pdf == true)<span> - pdf</span>@endif
                          @if($modalidade->jpg == true)<span> - jpg</span>@endif
                          @if($modalidade->jpeg == true)<span> - jpeg</span>@endif
                          @if($modalidade->png == true)<span> - png</span>@endif
                          @if($modalidade->docx == true)<span> - docx</span>@endif
                          @if($modalidade->odt == true)<span> - odt</span>@endif
                          @if($modalidade->zip == true)<span> - zip</span>@endif
                          @if($modalidade->svg == true)<span> - svg</span>@endif.
                        </small>
                        @error('arquivo'.$trabalho->id)
                          <span class="invalid-feedback" role="alert" style="overflow: visible; display:block">
                            <strong>{{ $message }}</strong>
                          </span>
                        @enderror
                      </div>
                    @endif
                  </div>
                @endif
                @if ($indice == "etiquetacampoextra1")
                  @if ($formSubTraba->checkcampoextra1 == true)
                    @if ($formSubTraba->tipocampoextra1 == "textosimples")
                      {{-- Texto Simples --}}
                      <div class="row justify-content-center">
                        {{-- Nome Trabalho  --}}
                        <div class="col-sm-12">
                              <label for="campoextra1simples_{{$trabalho->id}}" class="col-form-label">{{ $formSubTraba->etiquetacampoextra1}}:</label>
                              <input id="campoextra1simples_{{$trabalho->id}}" type="text" class="form-control @error('campoextra1simples') is-invalid @enderror" name="campoextra1simples" value="{{ old('campoextra1simples') }}" required autocomplete="campoextra1simples" autofocus>

                              @error('campoextra1simples')
                              <span class="invalid-feedback" role="alert">
                                  <strong>{{ $message }}</strong>
                              </span>
                              @enderror
                        </div>
                      </div>
                    @elseif ($formSubTraba->tipocampoextra1 == "textogrande")
                      {{-- Texto Grande --}}
                      <div class="row justify-content-center">
                      <div class="col-sm-12">
                            <label for="campoextra1grande" class="col-form-label">{{ $formSubTraba->etiquetacampoextra1}}:</label>
                            <textarea id="campoextra1grande" type="text" class="form-control @error('campoextra1grande') is-invalid @enderror" name="campoextra1grande" value="{{ old('campoextra1grande') }}" required autocomplete="campoextra1grande" autofocus></textarea>

                            @error('campoextra1grande')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                      </div>
                    @elseif ($formSubTraba->tipocampoextra1 == "upload")
                      <div class="col-sm-12" style="margin-top: 20px;">
                        <label for="campoextra1arquivo" class="col-form-label">{{ $formSubTraba->etiquetacampoextra1}}:</label>

                        <div class="custom-file">
                          <input type="file" class="filestyle" data-placeholder="Nenhum arquivo" data-text="Selecionar" data-btnClass="btn-primary-lmts" name="campoextra1arquivo" required>
                        </div>
                        <small>Algum texto aqui?</small>
                        @error('campoextra1arquivo')
                        <span class="invalid-feedback" role="alert" style="overflow: visible; display:block">
                          <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                      </div>
                    @endif
                  @endif
                @endif
                @if ($indice == "etiquetacampoextra2")
                  @if ($formSubTraba->checkcampoextra2 == true)
                    @if ($formSubTraba->tipocampoextra2 == "textosimples")
                      <div class="row justify-content-center">
                        {{-- Nome Trabalho  --}}
                      <div class="col-sm-12">
                            <label for="campoextra2simples" class="col-form-label">{{ $formSubTraba->etiquetacampoextra2}}:</label>
                            <input id="campoextra2simples" type="text" class="form-control @error('campoextra2simples') is-invalid @enderror" name="campoextra2simples" value="{{ old('campoextra2simples') }}" required autocomplete="campoextra2simples" autofocus>

                            @error('campoextra2simples')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                      </div>
                    @elseif ($formSubTraba->tipocampoextra2 == "textogrande")
                      <div class="row justify-content-center">
                        {{-- Nome Trabalho  --}}
                      <div class="col-sm-12">
                            <label for="campoextra2grande" class="col-form-label">{{ $formSubTraba->etiquetacampoextra2}}:</label>
                            <textarea id="campoextra2grande" type="text" class="form-control @error('campoextra2grande') is-invalid @enderror" name="campoextra2grande" value="{{ old('campoextra2grande') }}" required autocomplete="campoextra2grande" autofocus></textarea>

                            @error('campoextra2grande')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                      </div>
                    @elseif ($formSubTraba->tipocampoextra2 == "upload")
                      <div class="col-sm-12" style="margin-top: 20px;">
                        <label for="campoextra2arquivo" class="col-form-label">{{ $formSubTraba->etiquetacampoextra2}}:</label>

                        <div class="custom-file">
                          <input type="file" class="filestyle" data-placeholder="Nenhum arquivo" data-text="Selecionar" data-btnClass="btn-primary-lmts" name="campoextra2arquivo" required>
                        </div>
                        <small>Algum texto aqui?</small>
                        @error('campoextra2arquivo')
                        <span class="invalid-feedback" role="alert" style="overflow: visible; display:block">
                          <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                      </div>
                    @endif
                  @endif
                @endif
                @if ($indice == "etiquetacampoextra3")
                  @if ($formSubTraba->checkcampoextra3 == true)
                    @if ($formSubTraba->tipocampoextra3 == "textosimples")
                      <div class="row justify-content-center">
                        {{-- Nome Trabalho  --}}
                      <div class="col-sm-12">
                            <label for="campoextra3simples" class="col-form-label">{{ $formSubTraba->etiquetacampoextra3}}:</label>
                            <input id="campoextra3simples" type="text" class="form-control @error('campoextra3simples') is-invalid @enderror" name="campoextra3simples" value="{{ old('campoextra3simples') }}" required autocomplete="campoextra3simples" autofocus>

                            @error('campoextra3simples')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                      </div>
                    @elseif ($formSubTraba->tipocampoextra3 == "textogrande")
                      <div class="row justify-content-center">
                        {{-- Nome Trabalho  --}}
                      <div class="col-sm-12">
                            <label for="campoextra3grande" class="col-form-label">{{ $formSubTraba->etiquetacampoextra3}}:</label>
                            <textarea id="campoextra3grande" type="text" class="form-control @error('campoextra3grande') is-invalid @enderror" name="campoextra3grande" value="{{ old('campoextra3grande') }}" required autocomplete="campoextra3grande" autofocus></textarea>

                            @error('campoextra3grande')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                      </div>
                    @elseif ($formSubTraba->tipocampoextra3 == "upload")
                      {{-- Arquivo de Regras  --}}
                      <div class="col-sm-12" style="margin-top: 20px;">
                        <label for="campoextra3arquivo" class="col-form-label">{{ $formSubTraba->etiquetacampoextra3}}:</label>

                        <div class="custom-file">
                          <input type="file" class="filestyle" data-placeholder="Nenhum arquivo" data-text="Selecionar" data-btnClass="btn-primary-lmts" name="campoextra3arquivo" required>
                        </div>
                        <small>Algum texto aqui?</small>
                        @error('campoextra3arquivo')
                        <span class="invalid-feedback" role="alert" style="overflow: visible; display:block">
                          <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                      </div>
                    @endif
                  @endif
                @endif
                @if ($indice == "etiquetacampoextra4")
                  @if ($formSubTraba->checkcampoextra4 == true)
                    @if ($formSubTraba->tipocampoextra4 == "textosimples")
                      <div class="row justify-content-center">
                        {{-- Nome Trabalho  --}}
                      <div class="col-sm-12">
                            <label for="campoextra4simples" class="col-form-label">{{ $formSubTraba->etiquetacampoextra4}}:</label>
                            <input id="campoextra4simples" type="text" class="form-control @error('campoextra4simples') is-invalid @enderror" name="campoextra4simples" value="{{ old('campoextra4simples') }}" required autocomplete="campoextra4simples" autofocus>

                            @error('campoextra4simples')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                      </div>
                    @elseif ($formSubTraba->tipocampoextra4 == "textogrande")
                      <div class="row justify-content-center">
                        {{-- Nome Trabalho  --}}
                      <div class="col-sm-12">
                            <label for="campoextra4grande" class="col-form-label">{{ $formSubTraba->etiquetacampoextra4}}:</label>
                            <textarea id="campoextra4grande" type="text" class="form-control @error('campoextra4grande') is-invalid @enderror" name="campoextra4grande" value="{{ old('campoextra4grande') }}" required autocomplete="campoextra4grande" autofocus></textarea>

                            @error('campoextra4grande')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                      </div>
                    @elseif ($formSubTraba->tipocampoextra4 == "upload")
                      {{-- Arquivo de Regras  --}}
                      <div class="col-sm-12" style="margin-top: 20px;">
                        <label for="campoextra4arquivo" class="col-form-label">{{$formSubTraba->etiquetacampoextra4}}:</label>

                        <div class="custom-file">
                          <input type="file" class="filestyle" data-placeholder="Nenhum arquivo" data-text="Selecionar" data-btnClass="btn-primary-lmts" name="campoextra4arquivo" required>
                        </div>
                        <small>Algum texto aqui?</small>
                        @error('campoextra4arquivo')
                        <span class="invalid-feedback" role="alert" style="overflow: visible; display:block">
                          <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                      </div>
                    @endif
                  @endif
                @endif
                @if ($indice == "etiquetacampoextra5")
                  @if ($formSubTraba->checkcampoextra5 == true)
                    @if ($formSubTraba->tipocampoextra5 == "textosimples")
                      <div class="row justify-content-center">
                        {{-- Nome Trabalho  --}}
                      <div class="col-sm-12">
                            <label for="campoextra5simples" class="col-form-label">{{ $formSubTraba->etiquetacampoextra5}}:</label>
                            <input id="campoextra5simples" type="text" class="form-control @error('campoextra5simples') is-invalid @enderror" name="campoextra5simples" value="{{ old('campoextra5simples') }}" required autocomplete="campoextra5simples" autofocus>

                            @error('campoextra5simples')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                      </div>
                    @elseif ($formSubTraba->tipocampoextra5 == "textogrande")
                      <div class="row justify-content-center">
                        {{-- Nome Trabalho  --}}
                      <div class="col-sm-12">
                            <label for="campoextra5" class="col-form-label">{{ $formSubTraba->etiquetacampoextra5}}:</label>
                            <textarea id="campoextra5grande" type="text" class="form-control @error('campoextra5grande') is-invalid @enderror" name="campoextra5grande" value="{{ old('campoextra5grande') }}" required autocomplete="campoextra5grande" autofocus></textarea>

                            @error('campoextra5grande')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                      </div>
                    @elseif ($formSubTraba->tipocampoextra5 == "upload")
                      {{-- Arquivo de Regras  --}}
                      <div class="col-sm-12" style="margin-top: 20px;">
                        <label for="campoextra5arquivo" class="col-form-label">{{ $formSubTraba->etiquetacampoextra5}}:</label>

                        <div class="custom-file">
                          <input type="file" class="filestyle" data-placeholder="Nenhum arquivo" data-text="Selecionar" data-btnClass="btn-primary-lmts" name="campoextra5arquivo" required>
                        </div>
                        <small>Algum texto aqui?</small>
                        @error('campoextra5arquivo')
                        <span class="invalid-feedback" role="alert" style="overflow: visible; display:block">
                          <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                      </div>
                    @endif
                  @endif
                @endif
              @endforeach
            <br>
            {{-- <a href="{{route('downloadTrabalho', ['id' => $trabalho->id])}}" target="_new" class="m-2" style="font-size: 20px; color: #114048ff;" >
                <img class="" src="{{asset('img/icons/file-download-solid.svg')}}" style="width:20px">
            </a> --}}
                <br>
                <br>
                <button type="submit" class="btn btn-primary mr-4" form="formEditarTrab{{$trabalho->id}}">Salvar</button>
                <button type="button" class="btn btn-secondary" onclick="window.location='{{ route('coord.listarTrabalhos', ['eventoId' => $evento->id]) }}'">Cancelar</button>
            </form>
          </div>
    </div>

</div>

@endsection
@section('javascript')
@if(old('trabalhoEditId'))
  <script>
    $(document).ready(function() {
      $('#modalEditarTrabalho_{{old('trabalhoEditId')}}').modal('show');
    })
  </script>
@endif

<script>
    function handler() {
        autor = @json($trabalho->autor);
        coautores = @json($trabalho->coautors()->with('user')->get());
        oldEmail = @json(old('emailCoautor_' . $trabalho->id));
        oldNome = @json(old('nomeCoautor_' . $trabalho->id));
        inicial = [];
        if (oldEmail == null) {
            inicial.push({
                nome: autor.name,
                email: autor.email
            })
            for (let i = 0; i < coautores.length; i++) {
                inicial.push({
                    nome: coautores[i].user.name,
                    email: coautores[i].user.email
                });
            }
        } else {
            for (let i = 0; i < oldEmail.length; i++) {
                inicial.push({
                    nome: oldNome[i],
                    email: oldEmail[i]
                })
            }
        }
        return {
            autores: inicial,
            adicionaAutor() {
                this.autores.push({
                    nome: '',
                    email: ''
                });
            },
            removeAutor(index) {
                this.autores.splice(index, 1);
            },
            sobeAutor(index) {
                if (index > 1) {
                    temp = this.autores[index - 1]
                    this.autores[index - 1] = this.autores[index]
                    this.autores[index] = temp
                }
            },
            desceAutor(index) {
                if (index > 0 && (index + 1) < this.autores.length) {
                    temp = this.autores[index + 1]
                    this.autores[index + 1] = this.autores[index]
                    this.autores[index] = temp
                }
            }
        }
    }

    function checarNome(index) {
        let data = {
            email: $('#email' + index).val(),
            _token: '{{csrf_token()}}'
        };
        if (!(data.email == "" || data.email.indexOf('@') == -1 || data.email.indexOf('.') == -1)) {
            $.ajax({
                type: 'GET',
                url: '{{ route("search.user") }}',
                data: data,
                dataType: 'json',
                success: function (res) {
                    if (res.user[0] != null) {
                        $('#nome' + index).val(res.user[0]['name']);
                    }
                },
                error: function (err) {
                }
            });
        }
    }

    function centralizarTela(index) {
            if ($("#email" + index).length) {
                var el = $("#email" + index);
                el.focus();
                var center = $(window).height() / 2;
                var top = el.offset().top;
                if (top > center) {
                    $(window).scrollTop(top - center);
                }
            }
        }

  $(document).ready(function(){
    $('.char-count').keyup(function() {

        var maxLength = parseInt($(this).attr('maxlength'));
        var length = $(this).val().length;
        // var newLength = maxLength-length;

        var name = $(this).attr("name");
        $('#'+name).text(length);
    });
  });

  $(document).ready(function(){
    $('.palavra').keyup(function() {
        var myText = this.value.trim();
        var wordsArray = myText.split(/\s+/g);
        var words = wordsArray.length;
        var min = parseInt(($('#minpalavras').text()));
        var max = parseInt(($('#maxpalavras').text()));
        if(words < min || words > max) {
            this.setCustomValidity('Número de palavras não permitido. Você possui atualmente '+words+' palavras.');
        } else {
            this.setCustomValidity('');
        }
        var name = $(this).attr("name");
        $('#'+name).text(words);
    });
  });

</script>

@endsection
