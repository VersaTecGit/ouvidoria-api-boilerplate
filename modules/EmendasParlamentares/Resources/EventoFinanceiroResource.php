<?php

namespace Modules\EmendasParlamentares\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

final class EventoFinanceiroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agencia' => $this->agencia,
            'conta_corrente' => $this->conta_corrente,
            'tipo' => $this->tipo,
            'valor' => $this->valor,
            'observacao' => $this->observacao,
            'emenda_id' => $this->emenda_id,
        ];
    }
}