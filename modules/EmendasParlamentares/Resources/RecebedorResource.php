<?php

namespace Modules\EmendasParlamentares\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class RecebedorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'razao_social' => $this->razao_social,
            'cnpj' => $this->cnpj,
            'municipio' => $this->municipio,
            'uf' => $this->uf,
            'codigo_ibge' => $this->codigo_ibge,
        ];
    }
}