<?php

namespace Modules\EmendasParlamentares\Actions;

use Modules\EmendasParlamentares\Models\Recebedor;


final readonly class GetRecebedor
{
    public function handle(): array
    {
        return Recebedor::all()->toArray();
    }
}