<?php

declare(strict_types=1);

namespace Modules\Common\Core\Support;

use BadMethodCallException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @method static LengthAwarePaginator paginate(Collection $items, ?int $total = null, ?int $perPage = null, ?int $currentPage = null, ?string $path = null, ?string $pageName = 'page')
 */
final class Paginator
{
    private Collection $items;

    private ?int $total;

    private ?int $perPage;

    private ?int $currentPage;

    private ?string $path;

    private ?string $pageName;

    public function __construct(
        Collection $items,
        ?int $total = null,
        ?int $perPage = null,
        ?int $currentPage = null,
        ?string $path = null,
        ?string $pageName = 'page',
    ) {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
        $this->path = $path;
        $this->pageName = $pageName;
    }

    public function __call(string $name, array $arguments): mixed
    {
        if ($name === 'paginate') {
            return $this->handlePaginate(...$arguments);
        }

        throw new BadMethodCallException(sprintf('Method %s::%s does not exist.', self::class, $name));
    }

    public static function __callStatic(string $name, array $arguments): mixed
    {
        if ($name === 'paginate') {
            return call_user_func_array([new self(...$arguments), 'handlePaginate'], $arguments);
        }

        throw new BadMethodCallException(sprintf('Method %s::%s does not exist.', self::class, $name));
    }

    public static function from(Collection $items): Paginator
    {
        return new self($items);
    }

    public static function fromArray(array $items): Paginator
    {
        return new self(Collection::make($items));
    }

    public static function fromPaginatedResponse(Collection $response): Paginator
    {
        $items = $response->get('data') instanceof Collection
            ? $response->get('data')
            : Collection::make($response->get('data'));

        return new self(
            $items,
            $response->get('total'),
            $response->get('per_page'),
            $response->get('current_page'),
            $response->get('path'),
            $response->get('page_name'),
        );
    }

    public function total(int $total): Paginator
    {
        $this->total = $total;

        return $this;
    }

    public function perPage(int $perPage): Paginator
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function currentPage(int $currentPage): Paginator
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    public function path(string $path): Paginator
    {
        $this->path = $path;

        return $this;
    }

    public function pageName(string $pageName): Paginator
    {
        $this->pageName = $pageName;

        return $this;
    }

    public function handlePaginate(): LengthAwarePaginator
    {
        $currentPage = $this->currentPage ?: LengthAwarePaginator::resolveCurrentPage($this->pageName);

        return new LengthAwarePaginator(
            $this->total ? $this->items : $this->items->forPage($currentPage, $this->perPage)->values(),
            $this->total ?: $this->items->count(),
            $this->perPage,
            $currentPage,
            [
                'path' => $this->path ?: LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $this->pageName,
            ]
        );
    }
}
