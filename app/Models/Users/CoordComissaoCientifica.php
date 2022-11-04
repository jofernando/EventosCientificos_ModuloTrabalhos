<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CoordComissaoCientifica extends Pivot
{
    protected $table = 'coord_comissao_cientificas';

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'eventos_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\Users\User');
    }

    public function evento()
    {
        return $this->belongsTo('App\Models\Submissao\Evento', 'eventos_id');
    }
}
