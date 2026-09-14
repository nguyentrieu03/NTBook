<?php

namespace App\Domains\Category\Repositories;

use App\Domains\Category\Contracts\CategoryRepositoryInterface;
use App\Domains\Category\DTOs\{CreateCategoryDTO, UpdateCategoryDTO, CategoryFilterDTO};
use App\Models\Category;
use App\Support\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    protected function model(): string
    {
        return Category::class;
    }

    public function list(CategoryFilterDTO $filter): LengthAwarePaginator|Collection
    {
        $query = $this->query();

        if ($filter->search) {
            $term = '%' . $filter->search . '%';
            $query->where('name', 'like', $term);
        }

        $query->where('is_active', $filter->isActive);

        if($filter->withCount) {
            $query->withCount($filter->withCount);
        }

        foreach ($filter->sort as $field => $direction) {
            $query->orderBy($field, $direction);
        }

        return $filter->paginate ? $query
            ->paginate($filter->perPage) : $query->get();
    }

    public function create(CreateCategoryDTO $dto): Category
    {
        return $this->query()->create([
            'name'     => $dto->name,
            'slug'    => $dto->slug,
            'level' => $dto->level,
            'sort_order' => $dto->sortOrder,
            'is_active' => $dto->isActive,
            'parent_id' => $dto->parentId,
        ]);
    }

    public function update(Category $category, UpdateCategoryDTO $dto): Category
    {
        $category->fill($dto->toArray());
        $category->save();

        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool
    {
        return $this->query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    public function maxSortOrder(?int $parentId): ?int
    {
        return $this->query()
            ->when(
                $parentId === null,
                fn ($q) => $q->whereNull('parent_id'),
                fn ($q) => $q->where('parent_id', $parentId),
            )
            ->max('sort_order');
    }

    public function countByIds(array $ids): int 
    {
        return $this->query()->whereIn('id', $ids)->count();
    }

    public function updatePosition(int $id, ?int $parentId, int $sortOrder, int $level): int
    {
        return $this->query()
            ->where('id', $id)
            ->update([
                'parent_id' => $parentId,
                'sort_order' => $sortOrder,
                'level' => $level,
            ]);
    }

    public function hasChildren(Category $category): bool
    {
        return $category->children()->exists();
    }

    public function hasProducts(Category $category): bool
    {
        return $category->products()->exists();
    }
}
