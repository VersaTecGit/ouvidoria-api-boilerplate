<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Common\Core\DTOs\DatatableDTO;
use Modules\Common\Core\Support\Datatable;
use Modules\Questionnaires\Filters\QuestionnairesFilters;
use Modules\Questionnaires\Models\Questionnaire;

final readonly class FetchQuestionnairesList
{
    public function __construct(
        private QuestionnairesFilters $filters,
    ) {}

    public function handle(DatatableDTO $dto): LengthAwarePaginator|Collection
    {
        $query = Questionnaire::query()->with('questionnairesGroup')->whereNull('expired_at')->filtered($this->filters)->all();

        $query = Datatable::applyFilter($query, $dto, ['title', 'description']);
        $query = Datatable::applySort($query, $dto);

        return Datatable::applyPagination($query, $dto);
    }
}
