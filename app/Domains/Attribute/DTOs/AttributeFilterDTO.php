<?php

namespace App\Domains\Attribute\DTOs;

final class AttributeFilterDTO
{
    private const ALLOWED_SORT = ['id', 'name'];
    private const ALLOWED_WITH_COUNT = ['products', 'attributeValues'];
    private const ALLOWED_RELATIONS = [
        'products' => [
            'where_columns' => ['is_active'],
            'sort_columns' => ['id', 'value'],
        ],
        'attributeValues' => [
            'where_columns' => ['is_active'],
            'sort_columns' => ['id', 'value'],
        ],
    ];

    public function __construct(
        public readonly ?string $search = null,
        public readonly bool $isActive = true,
        public readonly array $sort = ['id' => 'desc'],
        public readonly int $perPage = 15,
        public readonly bool $paginate = true,
        public readonly ?array $withCount = null,
        public readonly ?array $relations = null,
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
            relations: self::normalizeRelations($data['relations'] ?? []),
        );
    }

    private static function normalizeSort(array $sort): array
    {
        if(!is_array($sort) || $sort === []) {
            return ['id' => 'desc'];
        }

        $normalized = [];
        foreach($sort as $field => $direction) {
            if(! in_array($field, self::ALLOWED_SORT, true)) {
                continue;
            }

            $normalized[$field] = strtolower(trim($direction)) === 'desc' ? 'desc' : 'asc';
        }

        return $normalized !== [] ? $normalized : ['id' => 'desc'];
    }

    private static function normalizeWithCount(array $withCount): array|null
    {
        if(!is_array($withCount) || $withCount === []) {
            return null;
        }

        $allowed = array_values(array_intersect($withCount, self::ALLOWED_WITH_COUNT));

        return $allowed !== [] ? $allowed : null;
    }

    private static function normalizeRelations(array $relations): array|null 
    {
        if(!is_array($relations) || $relations === []) {
            return null;
        }

        $normalized = [];
        foreach($relations as $relation => $conditions) {
            if(! isset(self::ALLOWED_RELATIONS[$relation]) || ! is_array($conditions)) {
                continue;
            }

            $rules = self::ALLOWED_RELATIONS[$relation];

            $normalized[$relation] = [
                'where' => self::normalizeRelationWhere($conditions['where'] ?? [], $rules['where_columns']),
                'sort' => self::normalizeRelationSort($conditions['sort'] ?? [], $rules['sort_columns']),
            ];
        }

        return $normalized !== [] ? $normalized : null;
    }

    private static function normalizeRelationWhere(array $whereArr, array $allowedWhereColumns): array {
        if(!is_array($whereArr) || $whereArr === []) {
            return [];
        }

        $normalized = [];
        foreach($whereArr as $where) {
            if(is_array($where) && count($where) === 3 && in_array($where[0], $allowedWhereColumns, true)) {
                $normalized[] = $where;
            }
        }

        return $normalized;
    }
    
    private static function normalizeRelationSort(array $sortArr, array $allowedSortColumns): array {
        if(!is_array($sortArr) || $sortArr === []) {
            return [];
        }

        $normalized = [];
        foreach($sortArr as $sortBy => $sortDirection) {
            if(! in_array($sortBy, $allowedSortColumns, true)) {
                continue;
            }

            $normalized[$sortBy] = strtolower(trim($sortDirection)) === 'desc' ? 'desc' : 'asc';
        }

        return $normalized !== [] ? $normalized : [];
    }
}