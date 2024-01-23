@extends('layouts.app')

@section('content')
    <div class="container-sm">
        <h2 class="text-center">Pagamento</h2>
        <h6 class="text-center mb-3">Preencha as informações para o pagamento, será gerado uma cobrança que poderá ser paga via PIX</h6>
        <form action="{{ route('checkout.processPayment') }}" method="post" class="mb-4">
            @csrf
            <input type="hidden" name="evento" value="{{ $evento->id }}">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="name">Nome</label>
                    <input type="text" class="form-control" id="name" name="name" required value="{{ auth()->user()->name }}" >
                </div>
                <div class="form-group col-md-6">
                    <label for="email">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required value="{{ auth()->user()->email }}">
                </div>
            </div>

            <div class="form-group" x-data="{ ehCpf: '{{ auth()->user()->cpf != null }}' }">
                <div class="custom-control custom-radio custom-control-inline col-form-label">
                    <input type="radio" id="customRadioInline1" name="ehCpf" value="1" x-model="ehCpf" class="custom-control-input" @if(auth()->user()->cpf != null) checked @endif>
                    <label class="custom-control-label" for="customRadioInline1">CPF</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="customRadioInline2" name="ehCpf" value="0" x-model="ehCpf" class="custom-control-input" @if(auth()->user()->cnpj != null) checked @endif>
                    <label class="custom-control-label " for="customRadioInline2">CNPJ</label>
                </div>

                <template x-if="ehCpf == 1">
                    <input type="text" class="form-control @error('cpf') is-invalid @enderror" name="cpf" value="{{ old('cpf', auth()->user()->cpf) }}">

                    @error('cpf')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </template>

                <template x-if="ehCpf == 0">
                    <input type="text" class="form-control @error('cnpj') is-invalid @enderror" name="cnpj" value="{{ old('cnpj', auth()->user()->cnpj) }}">

                    @error('cnpj')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </template>
            </div>

            {{-- Endereço --}}
            <h5 class="mt-2">{{__('Endereço')}}</h5>

            @php
                $end = auth()->user()->endereco;
            @endphp

            <div class="form-group">
                <label for="cep" class="col-form-label">{{ __('CEP') }}</label>
                <input id="cep" type="text" class="form-control @error('cep') is-invalid @enderror" name="cep" value="{{ old('cep', $end->cep) }}" required autocomplete="cep">

                @error('cep')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="rua" class="col-form-label">{{ __('Rua') }}</label>
                    <input id="rua" type="text" class="form-control @error('rua') is-invalid @enderror" name="rua" value="{{ old('rua', $end->rua) }}" required>

                    @error('rua')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group col-md-2">
                    <label for="numero" class="col-form-label">{{ __('Número') }}</label>
                    <input id="numero" type="text" class="form-control @error('numero') is-invalid @enderror" name="numero" value="{{ old('numero', $end->numero) }}" required autocomplete="numero" maxlength="10">

                    @error('numero')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group col-md-4">
                    <label for="bairro" class="col-form-label">{{ __('Bairro') }}</label>
                    <input id="bairro" type="text" class="form-control @error('bairro') is-invalid @enderror" name="bairro" value="{{ old('bairro', $end->bairro) }}" required>

                    @error('bairro')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>


            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="cidade" class="col-form-label">{{ __('Cidade') }}</label>
                    <input id="cidade" type="text" class="form-control @error('cidade') is-invalid @enderror" name="cidade" value="{{ old('cidade', $end->cidade) }}" required>

                    @error('cidade')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-4">
                    <label for="complemento" class="col-form-label">{{ __('Complemento') }}</label>
                    <input id="complemento" type="text" class="form-control apenasLetras @error('complemento') is-invalid @enderror" name="complemento" value="{{ old('complemento', $end->complemento) }}" required>

                    @error('complemento')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="col-sm-4" id="groupformuf">
                    <label for="uf" class="col-form-label">{{ __('UF') }}</label>
                    {{-- <input id="uf" type="text" class="form-control @error('uf') is-invalid @enderror" name="uf" value="{{ old('uf') }}" required autocomplete="uf" autofocus> --}}
                    <select class="form-control @error('uf') is-invalid @enderror" id="uf" name="uf">
                        <option value="" disabled selected hidden>-- UF --</option>
                        <option @if(old('uf', $end->uf) == 'AC') selected @endif value="AC">Acre</option>
                        <option @if(old('uf', $end->uf) == 'AL') selected @endif value="AL">Alagoas</option>
                        <option @if(old('uf', $end->uf) == 'AP') selected @endif value="AP">Amapá</option>
                        <option @if(old('uf', $end->uf) == 'AM') selected @endif value="AM">Amazonas</option>
                        <option @if(old('uf', $end->uf) == 'BA') selected @endif value="BA">Bahia</option>
                        <option @if(old('uf', $end->uf) == 'CE') selected @endif value="CE">Ceará</option>
                        <option @if(old('uf', $end->uf) == 'DF') selected @endif value="DF">Distrito Federal</option>
                        <option @if(old('uf', $end->uf) == 'ES') selected @endif value="ES">Espírito Santo</option>
                        <option @if(old('uf', $end->uf) == 'GO') selected @endif value="GO">Goiás</option>
                        <option @if(old('uf', $end->uf) == 'MA') selected @endif value="MA">Maranhão</option>
                        <option @if(old('uf', $end->uf) == 'MT') selected @endif value="MT">Mato Grosso</option>
                        <option @if(old('uf', $end->uf) == 'MS') selected @endif value="MS">Mato Grosso do Sul</option>
                        <option @if(old('uf', $end->uf) == 'MG') selected @endif value="MG">Minas Gerais</option>
                        <option @if(old('uf', $end->uf) == 'PA') selected @endif value="PA">Pará</option>
                        <option @if(old('uf', $end->uf) == 'PB') selected @endif value="PB">Paraíba</option>
                        <option @if(old('uf', $end->uf) == 'PR') selected @endif value="PR">Paraná</option>
                        <option @if(old('uf', $end->uf) == 'PE') selected @endif value="PE">Pernambuco</option>
                        <option @if(old('uf', $end->uf) == 'PI') selected @endif value="PI">Piauí</option>
                        <option @if(old('uf', $end->uf) == 'RJ') selected @endif value="RJ">Rio de Janeiro</option>
                        <option @if(old('uf', $end->uf) == 'RN') selected @endif value="RN">Rio Grande do Norte</option>
                        <option @if(old('uf', $end->uf) == 'RS') selected @endif value="RS">Rio Grande do Sul</option>
                        <option @if(old('uf', $end->uf) == 'RO') selected @endif value="RO">Rondônia</option>
                        <option @if(old('uf', $end->uf) == 'RR') selected @endif value="RR">Roraima</option>
                        <option @if(old('uf', $end->uf) == 'SC') selected @endif value="SC">Santa Catarina</option>
                        <option @if(old('uf', $end->uf) == 'SP') selected @endif value="SP">São Paulo</option>
                        <option @if(old('uf', $end->uf) == 'SE') selected @endif value="SE">Sergipe</option>
                        <option @if(old('uf', $end->uf) == 'TO') selected @endif value="TO">Tocantins</option>
                    </select>

                    @error('uf')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
            <div class="form-row justify-content-end">
                <button type="submit" class="btn btn-primary">Continuar</button>
            </div>
        </form>
    </div>
@endsection
