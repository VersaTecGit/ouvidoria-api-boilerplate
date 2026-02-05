<?php

namespace Modules\EmendasParlamentares\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;
use Modules\EmendasParlamentares\Filters\EmendaFilters;
use Modules\EmendasParlamentares\Models\Emenda;

final readonly class FetchEmendasList
{
    public function __construct(private EmendaFilters $filters){}

    public function handle(DatatableDTO $dto): LengthAwarePaginator|Collection
    {
        $query = Emenda::query()->filtered($this->filters)->all();
        $query = Datatable::applyFilter($query, $dto, ['numero', 'exercicio', 'tipo_objeto', 'status','responsavel', 'rascunho', 'concedente.nome', 'recebedor.razao_social']);
        $query = Datatable::applySort($query, $dto);

        return Datatable::applyPagination($query, $dto);
    }
}