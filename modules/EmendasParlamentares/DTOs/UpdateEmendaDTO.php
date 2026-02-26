<?php

namespace Modules\EmendasParlamentares\DTOs;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

class UpdateEmendaDTO extends ValidatedDTO
{

    public int $concedente_id;
    public int $recebedor_id;
    public int $modalidade_id;
    public ?bool $rascunho;
    public string $tipo_objeto;
    public string $status;
    public string $gnd;
    public string $descricao_objeto;
    public float $valor;
    public string $responsavel;
    public ?bool $anuencia_sus;

    public function rules() : array
    {
        return [
            'concedente_id' => 'nullable|integer|exists:concedentes,id',
            'recebedor_id' => 'required|integer|exists:recebedores,id',
            'modalidade_id' => 'required|integer|exists:modalidades_transferencias,id',
            'rascunho' => 'required|boolean',
            'tipo_objeto' => 'required|string|max:255|in:saude,educacao,infraestrutura,assistencia_social',
            'status' => 'required|string|in:pendente,aprovado,rejeitado,revisao',
            'gnd' => 'required|string|max:10|in:gnd3,gnd4,outro',
            'descricao_objeto' => 'required|string',
            'valor' => 'required|numeric|min:0',
            'responsavel' => 'required|string|max:255',
            'anuencia_sus' => 'nullable|boolean',
            'agencia' => 'nullable|required_with:eventos_financeiros.*.tipo|string|size:4',
            'conta_corrente' => 'nullable|required_with:eventos_financeiros.*.tipo|string|max:12',
            'eventos_financeiros' => 'array',
            'eventos_financeiros.*.id' => 'nullable|integer|exists:eventos_financeiros,id',
            'eventos_financeiros.*.data' => 'required|date',
            'eventos_financeiros.*.valor' => 'required|numeric|min:0',
            'eventos_financeiros.*.tipo' => 'required|string|in:disponibilizacao,empenho,liquidacao,pagamento',
            'eventos_financeiros.*.observacao' => 'nullable|string|max:255',
        ];
    }

    public function messages() : array
    {
        return [
            'concedente_id.required' => 'O ID do concedente é obrigatório.',
            'concedente_id.integer' => 'O ID do concedente deve ser um número inteiro.',
            'concedente_id.exists' => 'O concedente informado não existe.',
            'recebedor_id.required' => 'O ID do recebedor é obrigatório.',
            'recebedor_id.integer' => 'O ID do recebedor deve ser um número inteiro.',
            'recebedor_id.exists' => 'O recebedor informado não existe.',
            'modalidade_id.required' => 'O ID da modalidade é obrigatório.',
            'modalidade_id.integer' => 'O ID da modalidade deve ser um número inteiro.',
            'modalidade_id.exists' => 'A modalidade informada não existe.',
            'rascunho.required' => 'O campo rascunho é obrigatório.',
            'rascunho.boolean' => 'O campo rascunho deve ser verdadeiro ou falso.',
            'tipo_objeto.required' => 'O tipo do objeto é obrigatório.',
            'tipo_objeto.string' => 'O tipo do objeto deve ser uma string.',
            'tipo_objeto.max' => 'O tipo do objeto não pode exceder 255 caracteres.',
            'tipo_objeto.in' => 'O tipo do objeto informado não está entre uma das opções válidas.',
            'status.required' => 'O status é obrigatório.',
            'status.string' => 'O status deve ser uma string.',
            'status.in' => 'O status informado é não está entre uma das opções válidas.',
            'gnd.required' => 'O GND é obrigatório.',
            'gnd.string' => 'O GND deve ser uma string.',
            'gnd.max' => 'O GND não pode exceder 10 caracteres.',
            'gnd.in' => 'O GND informado não está entre uma das opções válidas.',
            'descricao_objeto.required' => 'A descrição do objeto é obrigatória.',
            'descricao_objeto.string' => 'A descrição do objeto deve ser uma string.',
            'valor.required' => 'O valor é obrigatório.',
            'valor.numeric' => 'O valor deve ser numérico.',
            'valor.min' => 'O valor deve ser maior ou igual a zero.',
            'responsavel.required' => 'O responsável é obrigatório.',
            'responsavel.string' => 'O responsável deve ser uma string.',
            'responsavel.max' => 'O responsável não pode exceder 255 caracteres.',
            'anuencia_sus.boolean' => 'O campo anuência SUS deve ser verdadeiro ou falso.',
        ];
    }

    public function defaults() : array
    {
        return [
            'anuencia_sus' => null,
        ];
    }

    public function casts() : array
    {
        return [];
    }

}