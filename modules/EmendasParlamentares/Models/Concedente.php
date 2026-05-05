<?php

declare(strict_types=1);

namespace Modules\EmendasParlamentares\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Concedente extends Model
{
    protected $table = 'concedentes';

    protected $fillable = [
        'nome',
        'partido',
        'tipo',
        'descricao',

    ];

    public function scopeForAutocompleteSearch(Builder $query, string $term): Builder
    {
        $pattern = '%'.$term.'%';

        return $query->where(function (Builder $inner) use ($pattern) {
            $inner->where('nome', 'ilike', $pattern)
                ->orWhere('partido', 'ilike', $pattern);
        });
    }
}