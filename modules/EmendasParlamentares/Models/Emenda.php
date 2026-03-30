<?php

namespace Modules\EmendasParlamentares\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Auth\Models\Concerns\Impersonation;
use Modules\Common\Core\Models\Concerns\CommonQueries;
use Modules\Common\Core\Models\Concerns\Filterable;

class Emenda extends Model
{
    use HasFactory,
        SoftDeletes,
        Filterable,
        Impersonation,
        CommonQueries;

    protected $table = 'emendas';

    protected $fillable = [
        'numero',
        'exercicio',
        'tipo_origem',
        'concedente_id',
        'recebedor_id',
        'modalidade_id',
        'rascunho',
        'tipo_objeto',
        'status',
        'gnd',
        'descricao_objeto',
        'valor',
        'responsavel',
        'anuencia_sus',
    ];

    public function modalidadeTransferencia() {
        return $this->belongsTo(ModalidadeTransferencia::class, 'modalidade_id');
    }

    public function concedente() {
        return $this->belongsTo(Concedente::class, 'concedente_id');
    }

    public function recebedor() {
        return $this->belongsTo(Recebedor::class, 'recebedor_id');
    }

    public function scopeAll(Builder $query): Builder
    {
        return $query->with(['modalidadeTransferencia', 'concedente', 'recebedor']);
    }

    public function eventosFinanceiros()
    {
        return $this->hasMany(EventoFinanceiro::class, 'emenda_id');
    }
}