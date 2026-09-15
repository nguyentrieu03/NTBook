<?php

namespace App\Domains\Category\DTOs;

final class CategoryFilterDTO
{
    private const ALLOWED_SORT = ['id', 'sort_order'];
    private const ALLOWED_WITH_COUNT = ['products'];

    public function __construct(
        public readonly ?string $search = null,
        public readonly bool $isActive = true,
        public readonly array $sort = ['id' => 'asc'],
        public readonly int $perPage = 15,
        public readonly bool $paginate = true,
        public readonly ?array $withCount = null,
    ){}

    public static function fromRequest(array $data): self
    {
        return new self(
            search: isset($data['search']) ? trim($data['search']) : null,
            isActive: (bool)($data['is_active'] ?? true),
            sort: self::normalizeSort($data['sort'] ?? []),
            perPage: min((int)($data['per_page'] ?? 15), 100),
            paginate: (bool)($data['paginate'] ?? true),
            withCount: self::normalizeWithCount($data['with_count'] ?? []),
        );
    }

    private static function normalizeSort(array $sort): array
    {
        if(!is_array($sort) || $sort === []) {
            return ['sort_order' => 'asc', 'id' => 'asc'];
        }

        $normalized = [];
        foreach($sort as $field => $direction) {
            if(! in_array($field, self::ALLOWED_SORT, true)) {
                continue;
            }

            $normalized[$field] = strtolower(trim($direction)) === 'desc' ? 'desc' : 'asc';
        }

        return $normalized !== [] ? $normalized : ['sort_order' => 'asc', 'id' => 'asc'];
    }

    private static function normalizeWithCount(array $withCount): array|null
    {
        if(!is_array($withCount) || $withCount === []) {
            return null;
        }

        $allowed = array_values(array_intersect($withCount, self::ALLOWED_WITH_COUNT));

        return $allowed !== [] ? $allowed : null;
    }
}