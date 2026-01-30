<?php

namespace Modules\EmendasParlamentares\DTOs;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

class CreateEventoFinanceiroDTO extends ValidatedDTO
{
    public string $agencia;
    public string $conta_corrente;
    public string $tipo;
    public float $valor;
    public ?string $observacao;
    public int $emenda_id;

    public function rules(): array
    {
        return [
            'agencia' => 'required|string|max:20',
            'conta_corrente' => 'required|string|max:20',
            'tipo' => 'required|string|max:100|in:Liquidacao,Disponibilizacao,Empenho,Pagamento',
            'valor' => 'required|numeric|min:0',
            'observacao' => 'nullable|string',
            'emenda_id' => 'required|integer|exists:emendas,id',
        ];
    }

    public function messages(): array
    {
        return [
            'agencia.required' => 'A agência é obrigatória.',
            'agencia.string' => 'A agência deve ser uma string.',
            'agencia.max' => 'A agência não pode exceder 20 caracteres.',
            'conta_corrente.required' => 'A conta corrente é obrigatória.',
            'conta_corrente.string' => 'A conta corrente deve ser uma string.',
            'conta_corrente.max' => 'A conta corrente não pode exceder 20 caracteres.',
            'tipo.required' => 'O tipo é obrigatório.',
            'tipo.string' => 'O tipo deve ser uma string.',
            'tipo.max' => 'O tipo não pode exceder 100 caracteres.',
            'valor.required' => 'O valor é obrigatório.',
            'valor.numeric' => 'O valor deve ser numérico.',
            'valor.min' => 'O valor deve ser maior ou igual a zero.',
            'observacao.string' => 'A observação deve ser uma string.',
            'emenda_id.required' => 'O ID da emenda é obrigatório.',
            'emenda_id.integer' => 'O ID da emenda deve ser um número inteiro.',
            'emenda_id.exists' => 'A emenda informada não existe.',
        ];
    }

    public function defaults(): array
    {
        return [
            'observacao' => null,
        ];
    }

    public function casts(): array
    {
        return [];
    }
}