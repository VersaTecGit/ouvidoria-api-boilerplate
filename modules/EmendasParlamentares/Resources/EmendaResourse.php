<?php
declare(strict_types=1);

namespace Modules\EmendasParlamentares\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

final class EmendaResourse extends JsonResource
{
    public function toArray(Request $request): array
    {
        $eventos = $this->eventosFinanceiros;
        $primeiroEvento = $eventos->first();

        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'exercicio' => $this->exercicio,
            'tipo_origem' => $this->tipo_origem,
            'concedente' => [
                'id' => $this->concedente_id,
                'nome' => $this->concedente->nome,
            ],
            'recebedor' => [
                'id' => $this->recebedor_id,
                'razao_social' => $this->recebedor->razao_social,
            ],
            'modalidade_id' => $this->modalidade_id,
            'rascunho' => $this->rascunho,
            'tipo_objeto' => $this->tipo_objeto,
            'status' => $this->status,
            'gnd' => $this->gnd,
            'descricao_objeto' => $this->descricao_objeto,
            'valor' => $this->valor,
            'responsavel' => $this->responsavel,
            'anuencia_sus' => $this->anuencia_sus,
            'agencia' => $primeiroEvento->agencia ?? null,
            'conta_corrente' => $primeiroEvento->conta_corrente ?? null,
            'eventos_financeiros' => $this->eventosFinanceiros()->get()->map(function ($evento) {
                return [
                    'id' => $evento->id,
                    'data' => $evento->data,
                    'valor' => $evento->valor,
                    'tipo' => $evento->tipo,
                    'observacao' => $evento->observacao,
                ];
            }),
        ];
    }
}