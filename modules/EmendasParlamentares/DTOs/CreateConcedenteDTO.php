<?php

namespace Modules\EmendasParlamentares\DTOs;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

class CreateConcedenteDTO extends ValidatedDTO
{   
    public string $nome;
    public ?string $partido;
    public string $tipo;
    public ?string $descricao;

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'partido' => 'nullable|string|max:100',
            'tipo' => 'required|string|max:100',
            'descricao' => 'nullable|string',
        ];
    }

   public function messages(): array
   {
       return [
           'nome.required' => 'O nome do concedente é obrigatório.',
           'nome.string' => 'O nome do concedente deve ser uma string.',
           'nome.max' => 'O nome do concedente não pode exceder 255 caracteres.',
           'partido.string' => 'O partido do concedente deve ser uma string.',
           'partido.max' => 'O partido do concedente não pode exceder 100 caracteres.',
           'tipo.required' => 'O tipo do concedente é obrigatório.',
           'tipo.string' => 'O tipo do concedente deve ser uma string.',
           'tipo.max' => 'O tipo do concedente não pode exceder 100 caracteres.',
           'descricao.string' => 'A descrição do concedente deve ser uma string.',
       ];
   }

   public function defaults(): array
   {
       return [
           'descricao' => null,
       ];
   }

   public function casts(): array
   {
       return [];
   }

}