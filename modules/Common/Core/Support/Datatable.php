<?php

declare(strict_types=1);

namespace Modules\Common\Core\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Common\Core\DTOs\DatatableDTO;

readonly class Datatable
{
    public const ALL_COLUMNS = ['*'];

    public const DEFAULT_PAGE_NAME = 'page';

    public static function applyPagination(
        Builder $builder,
        DatatableDTO $dto,
        array $columns = self::ALL_COLUMNS,
        string $pageName = self::DEFAULT_PAGE_NAME
    ): LengthAwarePaginator|Collection {
        return $dto->getAll()
            ? $builder->select($columns)->get()
            : $builder->paginate($dto->per_page, $columns, $pageName, $dto->page);
    }

    public static function applySort(Builder $builder, DatatableDTO $dto, array $relatedColumns = []): Builder
    {
        if (empty($dto->sort_field)) {
            return $builder;
        }

        if (array_key_exists($dto->sort_field, $relatedColumns)) {
            [$relation, $column] = explode('_', $dto->sort_field, 2);
            $relatedTable = $relatedColumns[$dto->sort_field];

            $builder->leftJoin($relatedTable, "{$relatedTable}.id", '=', "{$relation}_id");
            $sortField = "{$relatedTable}.{$column}";
        } else {
            $sortField = $dto->sort_field;
        }

        return $dto->sort_order->value === SortOption::ASC->value
            ? $builder->orderBy($sortField)
            : $builder->orderByDesc($sortField);
    }

    public static function applyFilter(Builder $builder, DatatableDTO $dto, array $fieldsToSearch): Builder
    {
        if (empty($dto->search) || empty($fieldsToSearch)) {
            return $builder;
        }

        return $builder->where(function (Builder $query) use ($dto, $fieldsToSearch) {
            foreach ($fieldsToSearch as $field) {
                $query->orwhereRaw("unaccent({$field}) ilike unaccent('%{$dto->search}%')");
            }
        });
    }
}
