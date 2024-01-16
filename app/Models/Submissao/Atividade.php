<?php

namespace App\Models\Submissao;

use App\Models\Users\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;

class Atividade extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'titulo', 'vagas', 'valor', 'descricao', 'local',
        'carga_horaria', 'palavras_chave', 'visibilidade_participante',
        'eventoId', 'tipo_id',
    ];

    public function evento()
    {
        return $this->belongsTo('App\Models\Submissao\Evento', 'eventoId');
    }

    public function tipoAtividade()
    {
        return $this->belongsTo('App\Models\Submissao\TipoAtividade', 'tipo_id');
    }

    public function convidados()
    {
        return $this->hasMany('App\Models\Users\Convidado', 'atividade_id');
    }

    public function datasAtividade()
    {
        return $this->hasMany('App\Models\Submissao\DatasAtividade', 'atividade_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'atividades_user', 'atividade_id', 'user_id');
    }

    public function atividadeInscricoesEncerradas()
    {
        if (! $this->visibilidade_participante) {
            return true;
        }
        $primeiraAtividade = $this->datasAtividade()->orderBy('data', 'ASC')->orderBy('hora_inicio', 'ASC')->first();
        $dataPrimeiraAtividade = new DateTime($primeiraAtividade->data.$primeiraAtividade->hora_inicio);
        if ($dataPrimeiraAtividade < now()) {
            $encerrada = true;
        } else {
            $encerrada = false;
        }

        return $encerrada;
    }

    public function terminou()
    {
        $dataAtividade = $this->datasAtividade()->orderBy('data', 'desc')->orderBy('hora_inicio', 'desc')->first();
        $dataFim = new Carbon($dataAtividade->data.' '.$dataAtividade->hora_fim);

        return now() > $dataFim;
    }
}
