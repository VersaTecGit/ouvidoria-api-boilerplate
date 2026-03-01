<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;
use Modules\Questionnaires\Filters\QuestionnairesGroupFilters;
use Modules\Questionnaires\Models\QuestionnairesGroup;

final readonly class FetchQuestionnairesGroupsList
{
    public function __construct(private QuestionnairesGroupFilters $filters) {}

    public function handle(DatatableDTO $dto): LengthAwarePaginator|Collection
    {
        $query = QuestionnairesGroup::filtered($this->filters)->all()->ordered();

        $query = Datatable::applyFilter($query, $dto, ['title', 'description']);
        $query = Datatable::applySort($query, $dto);

        return Datatable::applyPagination($query, $dto);
    }
}
