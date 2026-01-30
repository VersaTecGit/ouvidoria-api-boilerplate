<?php

namespace Modules\EmendasParlamentares\DTOs;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

final class CreateRecebedorDTO extends ValidatedDTO
{
    public string $razao_social;
    public string $tipo;
    public string $cnpj;
    public string $municipio;
    public string $uf;
    public string $codigo_ibge;

    public function rules (): array
    {
        return [
                'tipo' => 'required|string|max:255',
                'razao_social' => 'required|string|max:255',
                'cnpj' => 'required|string|size:14',
                'municipio' => 'required|string|max:255',
                'uf' => 'required|string|size:2',
                'codigo_ibge' => 'required|string|max:10',
            ];
    }

    public function messages (): array
    {
        return [
                'tipo.required' => 'O tipo do recebedor é obrigatório.',
                'tipo.string' => 'O tipo do recebedor deve ser uma string.',
                'tipo.max' => 'O tipo do recebedor não pode exceder 255 caracteres.',
                'razao_social.required' => 'A razão social do recebedor é obrigatória.',
                'razao_social.string' => 'A razão social do recebedor deve ser uma string.',
                'razao_social.max' => 'A razão social do recebedor não pode exceder 255 caracteres.',
                'cnpj.required' => 'O CNPJ do recebedor é obrigatório.',
                'cnpj.string' => 'O CNPJ do recebedor deve ser uma string.',
                'cnpj.size' => 'O CNPJ do recebedor deve ter exatamente 14 caracteres.',
                'municipio.required' => 'O município do recebedor é obrigatório.',
                'municipio.string' => 'O município do recebedor deve ser uma string.',
                'municipio.max' => 'O município do recebedor não pode exceder 255 caracteres.',
                'uf.required' => 'A UF do recebedor é obrigatória.',
                'uf.string' => 'A UF do recebedor deve ser uma string.',
                'uf.size' => 'A UF do recebedor deve ter exatamente 2 caracteres.',
                'codigo_ibge.required' => 'O código IBGE do recebedor é obrigatório.',
                'codigo_ibge.string' => 'O código IBGE do recebedor deve ser uma string.',
                'codigo_ibge.max' => 'O código IBGE do recebedor não pode exceder 10 caracteres.',
            ];
    }

    public function defaults(): array
    {
        return [];
    }

    public function casts(): array
    {
        return [];
    }
}