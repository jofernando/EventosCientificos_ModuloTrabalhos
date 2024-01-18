<?php

namespace App\Http\Controllers\Inscricao;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Submissao\EventoController;
use App\Models\Inscricao\CampoFormulario;
use App\Models\Inscricao\CategoriaParticipante;
use App\Models\Inscricao\CupomDeDesconto;
use App\Models\Inscricao\Inscricao;
use App\Models\Inscricao\Promocao;
use App\Models\Submissao\Atividade;
use App\Models\Submissao\Endereco;
use App\Models\Submissao\Evento;
use App\Notifications\InscricaoEvento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InscricaoController extends Controller
{
    public function inscritos(Evento $evento)
    {
        $this->authorize('isCoordenadorOrCoordenadorDaComissaoOrganizadora', $evento);
        $inscricoes = $evento->inscritos()->sortBy('finalizada');

        return view('coordenador.inscricoes.inscritos', compact('inscricoes', 'evento'));
    }

    public function formulario(Evento $evento)
    {
        $this->authorize('isCoordenadorOrCoordenadorDaComissaoOrganizadora', $evento);
        $campos = $evento->camposFormulario;

        return view('coordenador.inscricoes.formulario', compact('evento', 'campos'));
    }

    public function categorias(Evento $evento)
    {
        $this->authorize('isCoordenadorOrCoordenadorDaComissaoOrganizadora', $evento);
        $categorias = $evento->categoriasParticipantes;

        return view('coordenador.inscricoes.categorias', compact('evento', 'categorias'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $inscricao = Inscricao::find($id);

        foreach ($inscricao->camposPreenchidos as $campo) {
            switch ($campo->tipo) {
                case 'file':
                    $campoSalvo = $inscricao->camposPreenchidos()->where('campo_formulario_id', '=', $campo->id)->first();
                    if ($campoSalvo != null && Storage::disk()->exists($campoSalvo->pivot->valor)) {
                        Storage::delete($campoSalvo->pivot->valor);
                    }
                    break;
                case 'endereco':
                    $endereco = Endereco::find($campo->pivot->valor);
                    $endereco->delete();
                    break;
            }
            $campo->inscricoesFeitas()->detach($inscricao->id);
        }

        $pagamento = null;
        if ($inscricao->pagamento()->exists())
            $pagamento = $inscricao->pagamento;

        $inscricao->delete();
        if ($pagamento)
            $pagamento->delete();
    }

    public function cancelar(Inscricao $inscricao)
    {
        $evento = $inscricao->evento;
        $this->authorize('isCoordenadorOrCoordenadorDaComissaoOrganizadora', $evento);
        $this->destroy($inscricao->id);
        return redirect()->back()->with('message', 'Inscrição cancelada com sucesso!');
    }

    public function inscrever(Request $request)
    {
        auth()->user() != null;
        $evento = Evento::find($request->evento_id);
        if (Inscricao::where('user_id', auth()->user()->id)->where('evento_id', $evento->id)->exists()) {
            return redirect()->action([EventoController::class, 'show'], ['id' => $request->evento_id])->with('message', 'Inscrição já realizada.');
        }
        if ($evento->eventoInscricoesEncerradas()) {
            return redirect()->action([EventoController::class, 'show'], ['id' => $request->evento_id])->with('message', 'Inscrições encerradas.');
        }
        $categoria = CategoriaParticipante::find($request->categoria);
        $possuiFormulario = $evento->possuiFormularioDeInscricao();
        if ($possuiFormulario) {
            $validator = Validator::make($request->all(), ['categoria' => 'required']);
            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('abrirmodalinscricao', true);
            }
            $validator = $this->validarCamposExtras($request, $categoria);
            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('abrirmodalinscricao', true);
            }
        }
        $inscricao = new Inscricao();
        $inscricao->categoria_participante_id = $request->categoria;
        $inscricao->user_id = auth()->user()->id;
        $inscricao->evento_id = $request->evento_id;

        if ($categoria != null && $categoria->valor_total != 0) {
            $inscricao->finalizada = false;
            $inscricao->save();

            return redirect()->action([CheckoutController::class, 'telaPagamento'], ['evento' => $request->evento_id]);
        } else {
            $inscricao->finalizada = !$evento->formEvento->modvalidarinscricao;
            $inscricao->save();
            auth()->user()->notify(new InscricaoEvento($evento));
            if ($possuiFormulario) {
                $this->salvarCamposExtras($inscricao, $request, $categoria);
            }

            return redirect()->action([EventoController::class, 'show'], ['id' => $request->evento_id])->with('message', 'Inscrição realizada com sucesso');
        }
    }

    public function confirmar(Request $request, $id)
    {
        $evento = Evento::find($request->evento_id);

        return view('coordenador.programacao.pagamento', compact('evento'));
    }

    public function aprovar(Inscricao $inscricao)
    {
        $evento = $inscricao->evento;
        $this->authorize('isCoordenadorOrCoordenadorDaComissaoOrganizadora', $evento);

        $inscricao->finalizada = true;
        $inscricao->save();
        return redirect()->back()->with('message', 'Inscrição aprovada com sucesso!');
    }

    public function validarCamposExtras(Request $request, $categoria)
    {
        $regras = [];
        foreach ($categoria->camposNecessarios()->orderBy('tipo')->get() as $campo) {
            switch ($campo->tipo) {
                case 'email':
                    $regras['email-'.$campo->id] = $campo->obrigatorio ? 'required|string|email' : 'nullable|string|email';
                    break;
                case 'text':
                    $regras['text-'.$campo->id] = $campo->obrigatorio ? 'required|string' : 'nullable|string';
                    break;
                case 'file':
                    $regras['file-'.$campo->id] = $campo->obrigatorio ? 'required|file|max:2000' : 'nullable|file|max:2000';
                    break;
                case 'date':
                    $regras['date-'.$campo->id] = $campo->obrigatorio ? 'required|date' : 'nullable|date';
                    break;
                case 'endereco':
                    $regras['endereco-cep-'.$campo->id] = $campo->obrigatorio ? 'required' : 'nullable';
                    $regras['endereco-bairro-'.$campo->id] = $campo->obrigatorio ? 'required' : 'nullable';
                    $regras['endereco-rua-'.$campo->id] = $campo->obrigatorio ? 'required' : 'nullable';
                    $regras['endereco-complemento-'.$campo->id] = $campo->obrigatorio ? 'required' : 'nullable';
                    $regras['endereco-cidade-'.$campo->id] = $campo->obrigatorio ? 'required' : 'nullable';
                    $regras['endereco-uf-'.$campo->id] = $campo->obrigatorio ? 'required' : 'nullable';
                    $regras['endereco-numero-'.$campo->id] = $campo->obrigatorio ? 'required' : 'nullable';
                    break;
                case 'cpf':
                    $regras['cpf-'.$campo->id] = $campo->obrigatorio ? 'required|cpf' : 'nullable|cpf';
                    break;
                case 'contato':
                    $regras['contato-'.$campo->id] = $campo->obrigatorio ? 'required' : 'nullable';
                    break;
            }
        }

        $validator = Validator::make($request->all(), $regras);

        return $validator;
    }

    public function salvarCamposExtras($inscricao, Request $request, $categoria)
    {
        if ($request->revisandoInscricao != null) {
            foreach ($categoria->camposNecessarios()->orderBy('tipo')->get() as $campo) {
                if ($campo->tipo == 'email' && $request->input('email-'.$campo->id) != null) {
                    $inscricao->camposPreenchidos()->updateExistingPivot($campo->id, ['valor' => $request->input('email-'.$campo->id)]);
                } elseif ($campo->tipo == 'text' && $request->input('text-'.$campo->id) != null) {
                    $inscricao->camposPreenchidos()->updateExistingPivot($campo->id, ['valor' => $request->input('text-'.$campo->id)]);
                } elseif ($campo->tipo == 'file' && $request->file('file-'.$campo->id) != null) {
                    $campoSalvo = $inscricao->camposPreenchidos()->where('campo_formulario_id', '=', $campo->id)->first();
                    if ($campoSalvo != null && Storage::disk()->exists($campoSalvo->pivot->valor)) {
                        Storage::delete($campoSalvo->pivot->valor);
                    }

                    $path = Storage::putFileAs('eventos/'.$inscricao->evento->id.'/inscricoes/'.$inscricao->id.'/'.$campo->id, $request->file('file-'.$campo->id), $campo->titulo.'.pdf');

                    $inscricao->camposPreenchidos()->updateExistingPivot($campo->id, ['valor' => $path]);
                } elseif ($campo->tipo == 'date' && $request->input('date-'.$campo->id) != null) {
                    $inscricao->camposPreenchidos()->updateExistingPivot($campo->id, ['valor' => $request->input('date-'.$campo->id)]);
                } elseif ($campo->tipo == 'endereco' && $request->input('endereco-cep-'.$campo->id) != null) {
                    $campoSalvo = $inscricao->camposPreenchidos()->where('campo_formulario_id', '=', $campo->id)->first();
                    $endereco = Endereco::find($campoSalvo->pivot->valor);
                    $endereco->cep = $request->input('endereco-cep-'.$campo->id);
                    $endereco->bairro = $request->input('endereco-bairro-'.$campo->id);
                    $endereco->rua = $request->input('endereco-rua-'.$campo->id);
                    $endereco->complemento = $request->input('endereco-complemento-'.$campo->id);
                    $endereco->cidade = $request->input('endereco-cidade-'.$campo->id);
                    $endereco->uf = $request->input('endereco-uf-'.$campo->id);
                    $endereco->numero = $request->input('endereco-numero-'.$campo->id);
                    $endereco->update();
                    $inscricao->camposPreenchidos()->updateExistingPivot($campo->id, ['valor' => $endereco->id]);
                } elseif ($campo->tipo == 'cpf' && $request->input('cpf-'.$campo->id) != null) {
                    $inscricao->camposPreenchidos()->updateExistingPivot($campo->id, ['valor' => $request->input('cpf-'.$campo->id)]);
                } elseif ($campo->tipo == 'contato' && $request->input('contato-'.$campo->id) != null) {
                    $inscricao->camposPreenchidos()->updateExistingPivot($campo->id, ['valor' => $request->input('contato-'.$campo->id)]);
                }
            }
        } else {
            foreach ($categoria->camposNecessarios()->orderBy('tipo')->get() as $campo) {
                if ($campo->tipo == 'email' && $request->input('email-'.$campo->id) != null) {
                    $inscricao->camposPreenchidos()->attach($campo->id, ['valor' => $request->input('email-'.$campo->id)]);
                } elseif ($campo->tipo == 'text' && $request->input('text-'.$campo->id) != null) {
                    $inscricao->camposPreenchidos()->attach($campo->id, ['valor' => $request->input('text-'.$campo->id)]);
                } elseif ($campo->tipo == 'file' && $request->file('file-'.$campo->id) != null) {
                    $path = Storage::putFileAs('eventos/'.$inscricao->evento->id.'/inscricoes/'.$inscricao->id.'/'.$campo->id, $request->file('file-'.$campo->id), $campo->titulo.'.pdf');

                    $inscricao->camposPreenchidos()->attach($campo->id, ['valor' => $path]);
                } elseif ($campo->tipo == 'date' && $request->input('date-'.$campo->id) != null) {
                    $inscricao->camposPreenchidos()->attach($campo->id, ['valor' => $request->input('date-'.$campo->id)]);
                } elseif ($campo->tipo == 'endereco' && $request->input('endereco-cep-'.$campo->id) != null) {
                    $endereco = new Endereco();
                    $endereco->cep = $request->input('endereco-cep-'.$campo->id);
                    $endereco->bairro = $request->input('endereco-bairro-'.$campo->id);
                    $endereco->rua = $request->input('endereco-rua-'.$campo->id);
                    $endereco->complemento = $request->input('endereco-complemento-'.$campo->id);
                    $endereco->cidade = $request->input('endereco-cidade-'.$campo->id);
                    $endereco->uf = $request->input('endereco-uf-'.$campo->id);
                    $endereco->numero = $request->input('endereco-numero-'.$campo->id);
                    $endereco->save();
                    $inscricao->camposPreenchidos()->attach($campo->id, ['valor' => $endereco->id]);
                } elseif ($campo->tipo == 'cpf' && $request->input('cpf-'.$campo->id) != null) {
                    $inscricao->camposPreenchidos()->attach($campo->id, ['valor' => $request->input('cpf-'.$campo->id)]);
                } elseif ($campo->tipo == 'contato' && $request->input('contato-'.$campo->id) != null) {
                    $inscricao->camposPreenchidos()->attach($campo->id, ['valor' => $request->input('contato-'.$campo->id)]);
                }
            }
        }
    }

    public function downloadFileCampoExtra($idInscricao, $idCampo)
    {
        $inscricao = Inscricao::findOrFail($idInscricao);
        if (auth()->user()->can('isCoordenadorOrCoordenadorDaComissaoOrganizadora', $inscricao->evento) || auth()->user()->administradors()->exists()) {
            $caminho = $inscricao->camposPreenchidos()->where('campo_formulario_id', '=', $idCampo)->first()->pivot->valor;
            if (Storage::disk()->exists($caminho)) {
                return Storage::download($caminho);
            }

            return abort(404);
        }
        return abort(403);
    }
}
