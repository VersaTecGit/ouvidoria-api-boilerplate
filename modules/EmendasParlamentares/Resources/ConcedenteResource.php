<?php
declare(strict_types=1);

namespace Modules\EmendasParlamentares\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ConcedenteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'partido' => $this->partido,
            'tipo' => $this->tipo,
            'descricao' => $this->descricao,
        ];
    }
}