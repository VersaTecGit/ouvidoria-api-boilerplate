<?php

namespace Modules\EmendasParlamentares\Models;

use Illuminate\Database\Eloquent\Model;

class EventoFinanceiro extends Model
{
    protected $table = 'eventos_financeiros';

    protected $fillable = [
        'agencia',
        'conta_corrente',
        'tipo',
        'data',
        'valor',
        'observacao',
        'emenda_id'
    ];

    public function emenda() {
        return $this->belongsTo(Emenda::class, 'emenda_id');
    }
}