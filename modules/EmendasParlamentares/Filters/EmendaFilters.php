<?php

namespace Modules\EmendasParlamentares\Filters;

use Modules\Common\Core\Filters\Abstracts\Filters;
use Modules\Common\Core\Filters\WhereLikeFilter;

final class EmendaFilters extends Filters
{
    protected array $filters = [
        'numero' => WhereLikeFilter::class,
        'exercicio' => WhereLikeFilter::class,
        'tipo_objeto' => WhereLikeFilter::class,
        'status' => WhereLikeFilter::class,
        'responsavel' => WhereLikeFilter::class,
        'rascunho' => WhereLikeFilter::class,
    ];
}