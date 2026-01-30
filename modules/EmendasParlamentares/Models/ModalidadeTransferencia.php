<?php

namespace Modules\EmendasParlamentares\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModalidadeTransferencia extends Model
{

    use SoftDeletes;

    protected $table = 'modalidades_transferencias';

    protected $fillable = [
        'descricao',
    ];
}