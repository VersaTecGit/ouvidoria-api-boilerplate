<?php

declare(strict_types=1);

namespace Modules\EmendasParlamentares\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Recebedor extends Model
{
    protected $table = 'recebedores';

    protected $fillable = [
        'tipo',
        'razao_social',
        'cnpj',
        'municipio',
        'uf',
        'codigo_ibge',
    ];

    public function scopeForAutocompleteSearch(Builder $query, string $term): Builder
    {
        $digitsOnly = preg_replace('/\D/', '', $term) ?? '';
        $socialPattern = '%'.$term.'%';

        return $query->where(function (Builder $inner) use ($socialPattern, $digitsOnly) {
            $inner->where('razao_social', 'ilike', $socialPattern);

            if ($digitsOnly !== '') {
                $cnpjPattern = '%'.$digitsOnly.'%';
                $inner->orWhere('cnpj', 'like', $cnpjPattern);
            }
        });
    }
}