<?php

namespace Modules\EmendasParlamentares\Actions;

use Modules\EmendasParlamentares\Models\Concedente;

class GetConcedente
{
    public function handle() : array
    {
        $concedentes = Concedente::all()->toArray();
        return $concedentes;
    }
}