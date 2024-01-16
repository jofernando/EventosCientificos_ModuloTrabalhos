<?php

namespace App\Models\Inscricao;

use Illuminate\Database\Eloquent\Model;

class Inscricao extends Model
{
    protected $fillable = [
        'user_id', 'evento_id', 'pagamento_id', 'promocao_id', 'cupom_desconto_id', 'finalizada',
    ];

    public function evento()
    {
        return $this->belongsTo('App\Models\Submissao\Evento', 'evento_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Users\User', 'user_id');
    }

    public function pagamento()
    {
        return $this->belongsTo('App\Models\Inscricao\Pagamento', 'pagamento_id');
    }

    public function camposPreenchidos()
    {
        return $this->belongsToMany('App\Models\Inscricao\CampoFormulario', 'valor_campo_extras', 'inscricao_id', 'campo_formulario_id')->orderBy('campo_formulario_id')->withPivot('valor');
    }

    public function categoria()
    {
        return $this->belongsTo('App\Models\Inscricao\CategoriaParticipante', 'categoria_participante_id');
    }

    public function podeSubmeterTrabalho()
    {
        if ($this->categoria()->exists()) {
            return $this->finalizada && $this->categoria->permite_submissao;
        }
        return $this->finalizada;
    }
}
