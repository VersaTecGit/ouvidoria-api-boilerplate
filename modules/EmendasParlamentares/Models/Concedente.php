<?php

namespace Modules\EmendasParlamentares\Models;

use Illuminate\Database\Eloquent\Model;

class Concedente extends Model
{
    protected $table = 'concedentes';

    protected $fillable = [
        'nome',
        'partido',
        'tipo',
        'descricao'

    ];
}