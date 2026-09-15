<?php

namespace App\Domains\Category\Contracts;

use App\Domains\Category\DTOs\{CreateCategoryDTO, UpdateCategoryDTO, CategoryFilterDTO};
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Support\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function list(CategoryFilterDTO $filter): LengthAwarePaginator|Collection;

    public function create(CreateCategoryDTO $dto): Category;

    public function update(Category $category, UpdateCategoryDTO $dto): Category;

    public function delete(Category $category): void;

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool;
    public function maxSortOrder(?int $parentId): ?int;
    public function countByIds(array $ids): int;
    public function updatePosition(int $id, ?int $parentId, int $sortOrder, int $level): int;
    public function hasChildren(Category $category): bool;
    public function hasProducts(Category $category): bool;
}
