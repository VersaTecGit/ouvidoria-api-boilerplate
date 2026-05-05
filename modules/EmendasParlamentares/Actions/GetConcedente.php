<?php

declare(strict_types=1);

namespace Modules\EmendasParlamentares\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\EmendasParlamentares\Models\Concedente;

final readonly class GetConcedente
{
    private const DEFAULT_FILTER_LIMIT = 15;

    private const MAX_FILTER_LIMIT = 20;

    public function handle(Request $request): JsonResponse
    {
        $term = $this->autocompleteTermFromRequest($request);

        $data = $term !== ''
            ? $this->filteredRecords($term, $this->limitFromRequest($request))
            : Concedente::all()->toArray();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    private function autocompleteTermFromRequest(Request $request): string
    {
        $raw = $request->query('search') ?? $request->query('q');

        return is_string($raw) ? trim($raw) : '';
    }

    private function limitFromRequest(Request $request): ?int
    {
        $limit = $request->query('limit');

        return is_numeric($limit) ? (int) $limit : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function filteredRecords(string $term, ?int $limit): array
    {
        $effectiveLimit = $limit === null
            ? self::DEFAULT_FILTER_LIMIT
            : min(max($limit, 1), self::MAX_FILTER_LIMIT);

        return Concedente::query()
            ->forAutocompleteSearch($term)
            ->orderBy('nome')
            ->limit($effectiveLimit)
            ->get()
            ->toArray();
    }
}
