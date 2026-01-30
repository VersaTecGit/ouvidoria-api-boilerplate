<?php

namespace Modules\EmendasParlamentares\Models;

use Illuminate\Database\Eloquent\Model;

class Recebedor extends model
{
    protected $table = 'recebedores';

    protected $fillable = [
        'tipo',
        'razao_social',
        'cnpj',
        'municipio',
        'uf',
        'codigo_ibge'
    ];
}